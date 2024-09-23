<?php

namespace Phiki;

use Exception;

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
            $matched = $this->match($lineText);

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

            // Match found – process pattern rules and continue.
            $this->process($matched, $line, $lineText);
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

        foreach (end($this->patternStack)['patterns'] as $pattern) {
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
                $matched = $pattern->tryMatch($lineText, $this->linePosition);
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

    protected function resolve(string $reference): ?array
    {
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

        // todo!();
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

                        $matched = $capturePattern->tryMatch($group[0], $position);

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

                continue;
            }

            $this->tokens[$line][] = new Token(
                $this->scopeStack,
                $group[0],
                $offset,
                $offset + strlen($group[0]),
            );

            $this->linePosition = $offset + strlen($group[0]);

            if (isset($capture['name'])) {
                array_pop($this->scopeStack);
            }
        }
    }
}
