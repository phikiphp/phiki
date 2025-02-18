<?php

namespace Phiki;

use Phiki\Contracts\PatternInterface;

final class State
{
    protected array $patternStack = [];

    protected array $scopeStack = [];

    protected bool $hasActiveInjection = false;

    protected int $linePosition = 0;

    protected int $anchorPosition = -1;

    protected array $anchorPositionStack = [-1];

    protected bool $isFirstLine = true;

    public function pushScopes(array $scopes): void
    {
        $this->scopeStack = array_merge($this->scopeStack, $scopes);
    }

    public function pushScope(string $scope): void
    {
        $this->scopeStack[] = $scope;
    }

    public function getScopes(): array
    {
        return $this->scopeStack;
    }

    public function popScope(): void
    {
        if (count($this->scopeStack) > 1) {
            array_pop($this->scopeStack);
        }
    }

    public function hasActiveInjection(): bool
    {
        return $this->hasActiveInjection;
    }

    public function resetActiveInjection(): void
    {
        $this->hasActiveInjection = false;
    }

    public function setActiveInjection(): void
    {
        $this->hasActiveInjection = true;
    }

    public function pushPattern(PatternInterface $pattern): void
    {
        $this->patternStack[] = $pattern;
    }

    public function setPatterns(array $patterns): void
    {
        $this->patternStack = $patterns;
    }

    public function getPatterns(): array
    {
        return $this->patternStack;
    }

    public function getPattern(): PatternInterface
    {
        return end($this->patternStack);
    }

    public function popPattern(): PatternInterface
    {
        return array_pop($this->patternStack);
    }

    public function setLinePosition(int $position): void
    {
        $this->linePosition = $position;
    }

    public function getLinePosition(): int
    {
        return $this->linePosition;
    }

    public function setAnchorPosition(int $position): void
    {
        $this->anchorPosition = $position;
    }

    public function getAnchorPosition(): int
    {
        return $this->anchorPosition;
    }

    public function resetAnchorPositions(): void
    {
        $this->setAnchorPosition(-1);
    }

    public function pushAnchorPosition(int $position): void
    {
        $this->anchorPositionStack[] = $position;
    }

    public function popAnchorPosition(): int
    {
        return array_pop($this->anchorPositionStack);
    }

    public function setNotFirstLine(): void
    {
        $this->isFirstLine = false;
    }

    public function isFirstLine(): bool
    {
        return $this->isFirstLine;
    }
}
