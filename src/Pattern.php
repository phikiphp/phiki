<?php

namespace Phiki;

readonly class Pattern
{
    public function __construct(
        protected array $pattern,
    ) {}

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        if ($this->isOnlyPatterns()) {
            $closest = false;
            $offset = $linePosition;

            foreach ($this->getPatterns() as $pattern) {
                $pattern = new static($pattern);

                if ($pattern->isInclude()) {
                    $name = $pattern->getIncludeName();
                    $pattern = $tokenizer->resolve($name);

                    if ($pattern === null) {
                        throw new \Exception("Unknown reference [{$name}].");
                    }

                    $pattern = new Pattern($pattern);
                }

                $matchedPattern = $pattern->tryMatch($tokenizer, $lineText, $linePosition, $cannotExceed);

                if ($matchedPattern === false) {
                    continue;
                }

                if ($matchedPattern->offset() === $linePosition) {
                    return $matchedPattern;
                }

                if ($closest === false) {
                    $closest = $matchedPattern;
                    $offset = $matchedPattern->offset();

                    continue;
                }

                if ($matchedPattern->offset() < $offset) {
                    $closest = $matchedPattern;
                    $offset = $matchedPattern->offset();

                    continue;
                }
            }

            return $closest;
        }

        $regex = match (true) {
            $this->isMatch() => $this->pattern['match'],
            $this->isOnlyEnd() => $this->pattern['end'],
            $this->isBegin() => $this->pattern['begin'],
            default => dd($this),
        };

        if (preg_match('/'.str_replace('/', '\/', $regex).'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function getIncludeName(): string
    {
        if (! str_starts_with($this->pattern['include'], '#')) {
            return $this->pattern['include'];
        }

        return substr($this->pattern['include'], 1);
    }

    public function isInclude(): bool
    {
        return isset($this->pattern['include']);
    }

    public function isOnlyPatterns(): bool
    {
        return $this->hasPatterns() && ! $this->isMatch() && ! $this->isBegin() && ! $this->isOnlyEnd();
    }

    public function hasPatterns(): bool
    {
        return isset($this->pattern['patterns']) && count($this->pattern['patterns']) > 0;
    }

    public function getPatterns(): array
    {
        return $this->pattern['patterns'];
    }

    public function isMatch(): bool
    {
        return isset($this->pattern['match']);
    }

    public function isBegin(): bool
    {
        return isset($this->pattern['begin']);
    }

    public function isEnd(): bool
    {
        return isset($this->pattern['end']);
    }

    public function isOnlyEnd(): bool
    {
        return $this->isEnd() && ! $this->isMatch() && ! $this->isBegin();
    }

    public function hasCaptures(): bool
    {
        return isset($this->pattern['captures']);
    }

    public function hasBeginCaptures(): bool
    {
        return isset($this->pattern['beginCaptures']) || ($this->isBegin() && $this->hasCaptures());
    }

    public function hasEndCaptures(): bool
    {
        $captures = $this->pattern['endCaptures'] ?? $this->pattern['captures'] ?? [];

        return count($captures) > 0;
    }

    public function captures(): array
    {
        if ($this->isMatch()) {
            return $this->pattern['captures'] ?? [];
        }

        if ($this->isBegin()) {
            return $this->pattern['beginCaptures'] ?? $this->pattern['captures'] ?? [];
        }

        if ($this->isOnlyEnd()) {
            return $this->pattern['endCaptures'] ?? $this->pattern['captures'] ?? [];
        }

        return [];
    }

    public function getRawPattern(): array
    {
        return $this->pattern;
    }

    public function getEnd(): string
    {
        return $this->pattern['end'];
    }

    public function getEndCaptures(): array
    {
        return $this->pattern['endCaptures'] ?? $this->pattern['captures'] ?? [];
    }

    public function scopes(array $scopeStack): array
    {
        if (! isset($this->pattern['name'])) {
            return $scopeStack;
        }

        return [
            ...$scopeStack,
            $this->pattern['name'],
        ];
    }

    public function scope(): ?string
    {
        return $this->pattern['name'] ?? null;
    }
}
