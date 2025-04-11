<?php

namespace Phiki\Grammar;

use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Tokenizer;

final class ParsedGrammar extends Pattern implements PatternCollectionInterface
{
    /**
     * @param  Pattern[]  $patterns
     * @param  array<string, Pattern>  $repository
     * @param  Injections\Injection[]  $injections
     */
    public function __construct(
        public ?string $name,
        public string $scopeName,
        public array $patterns,
        public array $repository,
        public array $injections,
    ) {}

    /** @return Injections\Injection[] */
    public function getInjections(): array
    {
        return $this->injections;
    }

    public function hasInjections(): bool
    {
        return count($this->injections) > 0;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function hasPatterns(): bool
    {
        return count($this->patterns) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        return $tokenizer->matchUsing($lineText, $this->getPatterns());
    }

    public function resolve(string $reference): ?Pattern
    {
        return $this->repository[$reference] ?? null;
    }

    public function scope(): string
    {
        return $this->scopeName;
    }

    public static function fromArray(array $grammar): ParsedGrammar
    {
        $parser = new Parser;

        return $parser->parse($grammar);
    }

    public function wasInjected(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return sprintf('grammar: %s', $this->scopeName);
    }
}
