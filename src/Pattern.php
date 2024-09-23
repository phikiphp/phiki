<?php

namespace Phiki;

readonly class Pattern
{
    public function __construct(
        protected array $pattern,
    ) {}

    public function tryMatch(string $lineText, int $linePosition): MatchedPattern|false
    {
        $regex = match (true) {
            $this->isMatch() => $this->pattern['match'],
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
        return isset($this->pattern['patterns']) && ! $this->isMatch() && ! $this->isBegin();
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

    public function isWhile(): bool
    {
        return isset($this->pattern['while']);
    }

    public function hasCaptures(): bool
    {
        return isset($this->pattern['captures']) || isset($this->pattern['beginCaptures']) || isset($this->pattern['endCaptures']);
    }

    public function captures(): array
    {
        if ($this->isMatch()) {
            return $this->pattern['captures'] ?? [];
        }

        // todo!();
    }

    public function getRawPattern(): array
    {
        return $this->pattern;
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
