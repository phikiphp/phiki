<?php

namespace Phiki;

use Exception;
use PhpParser\Node\Expr\Cast\Array_;

class Tokenizer
{
    protected array $patternStack = [];

    protected array $scopeStack = [];

    protected array $tokens = [];

    protected int $linePosition = 0;

    public function __construct(
        protected array $grammar,
    ) {}

    public function tokenize(string $input): array
    {
        $this->tokens = [];
        $this->scopeStack = preg_split('/\s+/', $this->grammar['scopeName']);
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
            $root = new Pattern(end($this->patternStack));

            $matched = $this->match($lineText);
            $endIsMatched = false;

            // Some patterns will include `$self`. Since we're not fixing all patterns to match at the end of the previous match
            // we need to check if we're looking for an `end` pattern that is closer than the matched subpattern.
            // FIXME: Duplicate method call here, not great for performance.
            if ($matched !== false && $root->isOnlyEnd() && $root->tryMatch($this, $lineText, $this->linePosition) !== false) {
                $endMatched = $root->tryMatch($this, $lineText, $this->linePosition);

                if ($endMatched->offset() <= $matched->offset() && $endMatched->text() !== '') {
                    $matched = $endMatched;
                    $endIsMatched = true;
                }
            }

            // We didn't find a matching subpattern and we're looking for an `end` pattern.
            // If we find it on this line, we need to pop it off the stack and process the end pattern.
            if ($matched === false && $root->isOnlyEnd() && $matched = $root->tryMatch($this, $lineText, $this->linePosition)) {
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
        }
    }

    protected function matchUsing(string $lineText, array $patterns): MatchedPattern|false
    {
        $patternStack = $this->patternStack;

        $this->patternStack = [['patterns' => $patterns]];

        $matched = $this->match($lineText);

        $this->patternStack = $patternStack;

        return $matched;
    }

    protected function match(string $lineText): MatchedPattern|false
    {
        $closest = false;
        $offset = $this->linePosition;
        $root = new Pattern(end($this->patternStack));

        foreach ($root->getPatterns() as $pattern) {
            $pattern = new Pattern($pattern);

            if ($pattern->isInclude()) {
                $name = $pattern->getIncludeName();
                $pattern = $this->resolve($name);

                if ($pattern === null) {
                    throw new Exception("Unknown reference [{$name}].");
                }

                $pattern = new Pattern($pattern);
            }

            if ($pattern->isOnlyPatterns()) {
                $matched = $this->matchUsing($lineText, $pattern->getRawPattern()['patterns']);
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

    public function resolve(string $reference): ?array
    {
        if ($reference === '$self') {
            return $this->grammar;
        }

        return $this->grammar['repository'][$reference] ?? null;
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

        if ($matched->pattern->isMatch() && $matched->pattern->hasCaptures()) {
            if ($matched->pattern->scope()) {
                $this->scopeStack[] = $matched->pattern->scope();
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
        } elseif ($matched->pattern->isMatch()) {
            if ($matched->text() !== '') {
                $this->tokens[$line][] = new Token(
                    $matched->pattern->scopes($this->scopeStack),
                    $matched->text(),
                    $matched->offset(),
                    $matched->end(),
                );
            }

            $this->linePosition = $matched->end();
        }

        if ($matched->pattern->isBegin()) {
            if ($matched->pattern->scope()) {
                $this->scopeStack[] = $matched->pattern->scope();
            }

            if ($matched->pattern->hasBeginCaptures()) {
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

            $endPattern = new Pattern([
                'name' => $matched->pattern->scope(),
                'end' => $matched->pattern->getEnd(),
                'endCaptures' => $matched->pattern->getEndCaptures(),
                'patterns' => $matched->pattern->hasPatterns() ? $matched->pattern->getPatterns() : [],
            ]);

            if ($endPattern->hasPatterns()) {
                $this->patternStack[] = $endPattern->getRawPattern();
                return;
            }

            $endMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition);

            // If we can't see the `end` pattern, we should just return.
            if ($endMatched === false) {
                $this->patternStack[] = $endPattern->getRawPattern();
                
                return;
            }

            // If we can see the `end` pattern, we should process it.
            $this->process($endMatched, $line, $lineText);

            if ($matched->pattern->scope()) {
                array_pop($this->scopeStack);
            }
        }

        if ($matched->pattern->isOnlyEnd()) {
            // FIXME: This is a bit of hack. There's a bug somewhere that is incorrectly popping the end scope off
            // of the stack before we're done with that specific scope. This will prevent this from happening.
            if ($matched->pattern->scope() && ! in_array($matched->pattern->scope(), $this->scopeStack)) {
                $this->scopeStack[] = $matched->pattern->scope();
            }

            if ($matched->pattern->hasEndCaptures()) {
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
        $captures = $pattern->pattern->captures();

        foreach ($captures as $index => $capture) {
            $group = $pattern->getCaptureGroup($index);

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

            if (isset($capture['name'])) {
                $this->scopeStack[] = $capture['name'];
            }

            if (isset($capture['patterns'])) {               
                // Until we reach the end of the capture group.
                while ($this->linePosition < $groupEnd) {
                    $closest = false;
                    $closestOffset = $this->linePosition;

                    foreach ($capture['patterns'] as $capturePattern) {
                        $capturePattern = new Pattern($capturePattern);

                        if ($capturePattern->isInclude()) {
                            $name = $capturePattern->getIncludeName();
                            $capturePattern = $this->resolve($name);

                            if ($capturePattern === null) {
                                throw new Exception("Unknown reference [{$name}].");
                            }

                            $capturePattern = new Pattern($capturePattern);
                        }

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

                    if ($closest->pattern->isMatch()) {
                        $this->process($closest, $line, $lineText);
                    } elseif ($closest->pattern->isBegin()) {
                        if ($closest->pattern->scope()) {
                            $this->scopeStack[] = $closest->pattern->scope();
                        }

                        if ($closest->pattern->hasBeginCaptures()) {
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

                        $endPattern = new Pattern([
                            'name' => $closest->pattern->scope(),
                            'end' => $closest->pattern->getEnd(),
                            'endCaptures' => $closest->pattern->getEndCaptures(),
                            'patterns' => $closest->pattern->hasPatterns() ? $closest->pattern->getPatterns() : [],
                        ]);

                        if ($endPattern->hasPatterns()) {
                            $onlyPatternsPattern = new Pattern([
                                'patterns' => $endPattern->getPatterns(),
                            ]);

                            while ($this->linePosition < $groupEnd) {
                                $subPatternMatched = $onlyPatternsPattern->tryMatch($this, $lineText, $this->linePosition, $groupEnd);
                                $endIsMatched = false;

                                if ($subPatternMatched !== false && $endPattern->isOnlyEnd() && $endPattern->tryMatch($this, $lineText, $this->linePosition) !== false) {
                                    $endMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition);

                                    if ($endMatched->offset() <= $subPatternMatched->offset() && $endMatched->text() !== '') {
                                        $subPatternMatched = $endMatched;
                                        $endIsMatched = true;
                                    }
                                }

                                if ($subPatternMatched === false && $endPattern->isOnlyEnd() && $subPatternMatched = $endPattern->tryMatch($this, $lineText, $this->linePosition)) {
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
                            throw new Exception('Entered an unexpected path.');
                            
                            continue;
                        }

                        // If we can see the `end` pattern, we should process it.
                        $this->process($endMatched, $line, $lineText);

                        if ($matched->pattern->scope()) {
                            array_pop($this->scopeStack);
                        }
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

            if (isset($capture['name'])) {
                array_pop($this->scopeStack);
            }
        }
    }
}
