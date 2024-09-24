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

            if ($matched->offset() !== $matched->end()) {
                // Match found – process pattern rules and continue.
                $this->process($matched, $line, $lineText);
            }

            if ($endIsMatched && $root->scope()) {
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

    protected function process(MatchedPattern $matched, int $line, string $lineText, bool $popping = false): void
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
            $this->tokens[$line][] = new Token(
                $matched->pattern->scopes($this->scopeStack),
                $matched->text(),
                $matched->offset(),
                $matched->end(),
            );

            $this->linePosition = $matched->end();
        }

        if ($matched->pattern->isBegin()) {
            if ($matched->pattern->scope()) {
                $this->scopeStack[] = $matched->pattern->scope();
            }

            if ($matched->pattern->hasBeginCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    $matched->text(),
                    $matched->offset(),
                    $matched->end(),
                );

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
            if ($matched->pattern->hasEndCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    $matched->text(),
                    $matched->offset(),
                    $matched->end(),
                );
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

            $offset = $group[1];

            if ($this->linePosition > $offset) {
                continue;
            }

            if ($offset > $this->linePosition) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    substr($lineText, $this->linePosition, $offset - $this->linePosition),
                    $this->linePosition,
                    $offset,
                );

                $this->linePosition = $offset;
            }

            if (isset($capture['name'])) {
                $this->scopeStack[] = $capture['name'];
            }

            if (isset($capture['patterns'])) {
                $position = 0;

                // Until we reach the end of the capture group.
                while ($position < strlen($group[0])) {
                    $closest = false;
                    $closestOffset = $position;

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

                        $matched = $capturePattern->tryMatch($this, $group[0], $position);

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

                    if ($closest === false) {
                        $this->tokens[$line][] = new Token(
                            $this->scopeStack,
                            substr($group[0], $position),
                            $this->linePosition + $position,
                            $offset + strlen($group[0]),
                        );

                        $this->linePosition = $offset + strlen($group[0]);

                        break;
                    }

                    if ($closest->offset() > $position) {
                        $this->tokens[$line][] = new Token(
                            $this->scopeStack,
                            substr($group[0], $position, $closest->offset() - $position),
                            $offset + $position,
                            $offset + $closest->offset(),
                        );

                        $position = $closest->offset();
                    }

                    $position = $closest->end();

                    $this->tokens[$line][] = new Token(
                        $closest->pattern->scopes($this->scopeStack),
                        $closest->text(),
                        $this->linePosition + $closest->offset(),
                        $this->linePosition + $position,
                    );
                }

                $this->linePosition = $offset + strlen($group[0]);
            }

            if ($this->linePosition < $offset + strlen($group[0])) {
                $this->tokens[$line][] = new Token(
                    $this->scopeStack,
                    $group[0],
                    $offset,
                    $offset + strlen($group[0]),
                );

                $this->linePosition = $offset + strlen($group[0]);
            }

            if (isset($capture['name'])) {
                array_pop($this->scopeStack);
            }
        }
    }
}
