<?php

namespace Phiki;

readonly class Pattern
{
    public function __construct(
        protected array $pattern,
    ) {}

    public function tryMatch(string $lineText, int $linePosition): MatchedPattern|false
    {
        if ($this->isOnlyPatterns()) {
            foreach ($this->getPatterns() as $pattern) {
                $matchedPattern = (new static($pattern))->tryMatch($lineText, $linePosition);

                if ($matchedPattern !== false) {
                    return $matchedPattern;
                }
            }
        }

        $regex = match (true) {
            $this->isMatch() => $this->pattern['match'],
            $this->isOnlyEnd() => $this->pattern['end'],
            $this->isBegin() => $this->pattern['begin'],
        };

        if (preg_match('/'.str_replace('/', '\/', $regex).'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function getIncludeName(): string
    {
        return substr($this->pattern['include'], 1);
    }

    public function isInclude(): bool
    {
        return isset($this->pattern['include']);
    }

    public function isOnlyPatterns(): bool
    {
        return $this->hasPatterns() && ! $this->isMatch() && ! $this->isBegin();
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
