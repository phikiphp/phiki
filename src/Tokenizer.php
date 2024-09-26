<?php

namespace Phiki;

use Exception;
use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\GrammarRepositoryInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Exceptions\IndeterminateStateException;
use Phiki\Exceptions\UnreachableException;
use Phiki\Grammar\BeginEndPattern;
use Phiki\Grammar\CollectionPattern;
use Phiki\Grammar\EndPattern;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\Pattern;
use Phiki\Grammar\IncludePattern;
use Phiki\Grammar\MatchPattern;

class Tokenizer
{
    protected array $patternStack = [];

    protected array $scopeStack = [];

    protected array $beginStack = [];

    protected array $tokens = [];

    protected int $linePosition = 0;

    public function __construct(
        protected Grammar $grammar,
        protected GrammarRepositoryInterface $grammarRepository = new GrammarRepository,
    ) {}

    public function tokenize(string $input): array
    {
        $this->tokens = [];
        $this->scopeStack = preg_split('/\s+/', $this->grammar->scopeName);
        $this->patternStack = [$this->grammar];

        $lines = preg_split("/\R/", $input);

        foreach ($lines as $line => $lineText) {
            $this->tokenizeLine($line, $lineText."\n");
        }

        return $this->tokens;
    }

    public function tokenizeLine(int $line, string $lineText): void
    {
        $this->linePosition = 0;

        while ($this->linePosition < strlen($lineText)) {
            $root = end($this->patternStack);
            $matched = $this->match($lineText);
            $endIsMatched = false;

            // Some patterns will include `$self`. Since we're not fixing all patterns to match at the end of the previous match
            // we need to check if we're looking for an `end` pattern that is closer than the matched subpattern.
            // FIXME: Duplicate method call here, not great for performance.
            if ($matched !== false && $root instanceof EndPattern && $root->tryMatch($this, $lineText, $this->linePosition) !== false) {
                $endMatched = $root->tryMatch($this, $lineText, $this->linePosition);

                if ($endMatched->offset() <= $matched->offset() && $endMatched->text() !== '') {
                    $matched = $endMatched;
                    $endIsMatched = true;
                }
            }

            // We didn't find a matching subpattern and we're looking for an `end` pattern.
            // If we find it on this line, we need to pop it off the stack and process the end pattern.
            if ($matched === false && $root instanceof EndPattern && $matched = $root->tryMatch($this, $lineText, $this->linePosition)) {
                $endIsMatched = true;
            }

            // No match found, advance to the end of the line.
            if ($matched === false) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    substr($lineText, $this->linePosition),
                    $this->linePosition,
                    strlen($lineText) - 1,
                );

                break;
            }

            // We've found a match for an `end` here. We need to remove it from the stack.
            // It's important that we do this here since we don't want to have an effect
            // on any capture patterns etc.
            if ($endIsMatched) {
                array_pop($this->patternStack);
            }

            // Match found – process pattern rules and continue.
            $this->process($matched, $line, $lineText);

            if ($endIsMatched && $root->scope() && count($this->scopeStack) > 1) {
                array_pop($this->scopeStack);
            }

            if ($endIsMatched) {
                array_pop($this->beginStack);
            }
        }
    }

    public function matchUsing(string $lineText, array $patterns): MatchedPattern|false
    {
        $patternStack = $this->patternStack;

        $this->patternStack = [new CollectionPattern($patterns)];

        $matched = $this->match($lineText);

        $this->patternStack = $patternStack;

        return $matched;
    }

    public function match(string $lineText): MatchedPattern|false
    {
        $closest = false;
        $offset = $this->linePosition;
        $root = end($this->patternStack);

        if (! $root instanceof PatternCollectionInterface) {
            throw new IndeterminateStateException('Root patterns must contain child patterns and implement ' . PatternCollectionInterface::class);
        }

        foreach ($root->getPatterns() as $pattern) {
            if ($pattern instanceof CollectionPattern) {
                $matched = $this->matchUsing($lineText, $pattern->getPatterns());
            } else {
                $matched = $pattern->tryMatch($this, $lineText, $this->linePosition);
            }

            // No match found. Move on to next pattern.
            if ($matched === false) {
                continue;
            }

            // Match found and is same as current position. Return it.
            if ($matched->offset() === $this->linePosition) {
                return $matched;
            }

            // First match found. Set it as the closest one.
            if ($closest === false) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }

            // Match found, closer than previous one.
            if ($matched->offset() < $offset) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }
        }

        return $closest;
    }

    public function resolve(IncludePattern $pattern): Pattern
    {
        // "include": "$self"
        if ($pattern->isSelf()) {
            return $this->grammarRepository->getFromScope($pattern->getScopeName() ?? $this->grammar->scopeName);
        }

        // "include": "$base"
        if ($pattern->isBase()) {
            return $this->grammar;
        }

        // "include": "#name"
        if ($pattern->getReference() && $pattern->getScopeName() === $this->grammar->scopeName) {
            return $this->grammar->resolve($pattern->getReference());
        }

        // "include": "scope#name"
        if ($pattern->getReference() && $pattern->getScopeName() !== $this->grammar->scopeName) {
            return $this->grammarRepository->getFromScope($pattern->getScopeName())->resolve($pattern->getReference());
        }
        
        // "include": "scope"
        return $this->grammarRepository->getFromScope($this->grammar->scopeName);
    }

    protected function process(MatchedPattern $matched, int $line, string $lineText): void
    {
        if ($matched->offset() > $this->linePosition) {
            $this->tokens[$line][] = new Token(
                $this->scopeStack,
                substr($lineText, $this->linePosition, $matched->offset() - $this->linePosition),
                $this->linePosition,
                $matched->offset(),
            );

            $this->linePosition = $matched->offset();
        }

        if ($matched->pattern instanceof MatchPattern && $matched->pattern->hasCaptures()) {
            if ($matched->pattern->scope()) {
                $this->scopeStack[] = $this->processScope($matched->pattern->scope(), $matched);
            }

            $this->captures($matched, $line, $lineText);

            if ($this->linePosition < $matched->end()) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    substr($lineText, $this->linePosition, $matched->end() - $this->linePosition),
                    $this->linePosition,
                    $matched->end(),
                );

                $this->linePosition = $matched->end();
            }

            if ($matched->pattern->scope()) {
                array_pop($this->scopeStack);
            }
        } elseif ($matched->pattern instanceof MatchPattern) {
            if ($matched->text() !== '') {
                $this->tokens[$line][] = new Token(
                    $matched->pattern->produceScopes($this->scopeStack),
                    $matched->text(),
                    $matched->offset(),
                    $matched->end(),
                );
            }

            $this->linePosition = $matched->end();
        }

        if ($matched->pattern instanceof BeginEndPattern) {
            $this->beginStack[] = $matched;

            if ($matched->pattern->scope()) {
                $this->scopeStack[] = $this->processScope($matched->pattern->scope(), $matched);
            }

            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->scopeStack,
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }

                $this->linePosition = $matched->end();
            }

            $endPattern = $matched->pattern->createEndPattern();

            if ($endPattern->hasPatterns()) {
                $this->patternStack[] = $endPattern;
                return;
            }

            $endMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition);

            // If we can't see the `end` pattern, we should just return.
            if ($endMatched === false) {
                $this->patternStack[] = $endPattern;
                
                return;
            }

            // If we can see the `end` pattern, we should process it.
            $this->process($endMatched, $line, $lineText);

            if ($matched->pattern->scope()) {
                array_pop($this->scopeStack);
            }

            array_pop($this->beginStack);
        }

        if ($matched->pattern instanceof EndPattern) {
            // FIXME: This is a bit of hack. There's a bug somewhere that is incorrectly popping the end scope off
            // of the stack before we're done with that specific scope. This will prevent this from happening.
            if ($matched->pattern->scope() && ! in_array($this->processScope($matched->pattern->scope(), end($this->beginStack)), $this->scopeStack)) {
                $this->scopeStack[] = $this->processScope($matched->pattern->scope(), end($this->beginStack));
            }

            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->scopeStack,
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }
            }

            $this->linePosition = $matched->end();
        }
    }

    protected function captures(MatchedPattern $pattern, int $line, string $lineText): void
    {
        if (! $pattern->pattern instanceof ContainsCapturesInterface) {
            throw new IndeterminateStateException("Patterns must implement " . ContainsCapturesInterface::class . " in order to process captures.");
        }

        $captures = $pattern->pattern->getCaptures();

        foreach ($captures as $capture) {
            $group = $pattern->getCaptureGroup($capture->index);

            if ($group === null) {
                continue;
            }

            $groupLength = strlen($group[0]);
            $groupStart = $group[1];
            $groupEnd = $group[1] + $groupLength;

            if ($this->linePosition > $groupStart) {
                continue;
            }

            if ($groupStart > $this->linePosition) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    substr($lineText, $this->linePosition, $groupStart - $this->linePosition),
                    $this->linePosition,
                    $groupStart,
                );

                $this->linePosition = $groupStart;
            }

            if ($capture->scope()) {
                $this->scopeStack[] = $this->processScope($capture->scope(), $pattern);
            }

            if ($capture->hasPatterns()) {            
                // Until we reach the end of the capture group.
                while ($this->linePosition < $groupEnd) {
                    $closest = false;
                    $closestOffset = $this->linePosition;

                    foreach ($capture->getPatterns() as $capturePattern) {
                        $matched = $capturePattern->tryMatch($this, $lineText, $this->linePosition, cannotExceed: $groupEnd);

                        // No match found. Move on to next pattern.
                        if ($matched === false) {
                            continue;
                        }

                        // Match found and is same as current position. Return it.
                        if ($matched->offset() === $this->linePosition) {
                            $closest = $matched;
                            $closestOffset = $matched->offset();

                            break;
                        }

                        // First match found. Set it as the closest one.
                        if ($closest === false) {
                            $closest = $matched;
                            $closestOffset = $matched->offset();

                            continue;
                        }

                        // Match found, closer than previous one.
                        if ($matched->offset() < $closestOffset) {
                            $closest = $matched;
                            $closestOffset = $matched->offset();

                            continue;
                        }
                    }

                    // No match found for this capture groups set of subpatterns.
                    // Advance to the end of the capture group.
                    if ($closest === false) {
                        $this->tokens[$line][] = new Token(
                            $this->scopeStack,
                            substr($lineText, $this->linePosition, $groupEnd - $this->linePosition),
                            $this->linePosition,
                            $groupEnd,
                        );

                        $this->linePosition = $groupEnd;

                        break;
                    }

                    if ($closest->pattern instanceof MatchPattern) {
                        $this->process($closest, $line, $lineText);
                    } elseif ($closest->pattern instanceof BeginEndPattern) {
                        $this->beginStack[] = $closest;

                        if ($closest->pattern->scope()) {
                            $this->scopeStack[] = $this->processScope($closest->pattern->scope(), $closest);
                        }

                        if ($closest->pattern->hasCaptures()) {
                            $this->captures($closest, $line, $lineText);
                        } else {
                            if ($closest->text() !== '') {
                                $this->tokens[$line][] = new Token(
                                    $this->scopeStack,
                                    $closest->text(),
                                    $closest->offset(),
                                    $closest->end(),
                                );
                            }

                            $this->linePosition = $closest->end();
                        }

                        $endPattern = $closest->pattern->createEndPattern();

                        if ($endPattern->hasPatterns()) {
                            $onlyPatternsPattern = new CollectionPattern($endPattern->getPatterns());

                            while ($this->linePosition < $groupEnd) {
                                $subPatternMatched = $onlyPatternsPattern->tryMatch($this, $lineText, $this->linePosition, $groupEnd);
                                $endIsMatched = false;

                                if ($subPatternMatched !== false && $endPattern instanceof EndPattern && $endPattern->tryMatch($this, $lineText, $this->linePosition) !== false) {
                                    $endMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition);

                                    if ($endMatched->offset() <= $subPatternMatched->offset() && $endMatched->text() !== '') {
                                        $subPatternMatched = $endMatched;
                                        $endIsMatched = true;
                                    }
                                }

                                if ($subPatternMatched === false && $endPattern instanceof EndPattern && $subPatternMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition)) {
                                    $endIsMatched = true;
                                }

                                // No subpatterns matched. End not matched, consume the line.
                                if ($subPatternMatched === false) {
                                    $this->tokens[$line][] = new Token(
                                        $this->scopeStack,
                                        substr($lineText, $this->linePosition, $groupEnd - $this->linePosition),
                                        $this->linePosition,
                                        $groupEnd,
                                    );
                                }

                                $this->process($subPatternMatched, $line, $lineText);

                                if ($subPatternMatched->pattern->scope()) {
                                    array_pop($this->scopeStack);
                                }

                                if ($endIsMatched && $endPattern->scope()) {
                                    array_pop($this->scopeStack);
                                }
                            }

                            continue;
                        }

                        $endMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition);

                        // If we can't see the `end` pattern, we should just continue.
                        if ($endMatched === false) {
                            throw new UnreachableException("End pattern cannot be found.");
                        }

                        // If we can see the `end` pattern, we should process it.
                        $this->process($endMatched, $line, $lineText);

                        if ($closest->pattern->scope()) {
                            array_pop($this->scopeStack);
                        }

                        array_pop($this->beginStack);
                    }
                }

                $this->linePosition = $groupEnd;
            }

            if ($this->linePosition < $groupEnd) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    $group[0],
                    $groupStart,
                    $groupEnd,
                );

                $this->linePosition = $groupEnd;
            }

            if ($capture->scope()) {
                array_pop($this->scopeStack);
            }
        }
    }

    protected function processScope(string $scope, MatchedPattern $pattern): string
    {
        return preg_replace_callback('/\\$(\d+)/', function ($matches) use ($pattern) {
            $group = $pattern->getCaptureGroup($matches[1]);

            if ($group === null) {
                return $matches[0];
            }

            return $group[0];
        }, $scope);
    }
}
