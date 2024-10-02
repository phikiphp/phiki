<?php

namespace Phiki\Grammar\Injections;

use Phiki\Contracts\InjectionMatcherInterface;

class Composite implements InjectionMatcherInterface
{
    /**
     * @param  array<Expression>  $expressions
     */
    public function __construct(
        public array $expressions,
    ) {}

    public function getPrefix(array $scopes): ?Prefix
    {
        if (! $this->matches($scopes)) {
            return null;
        }

        return $this->expressions[0]->getPrefix($scopes);
    }

    public function matches(array $scopes): bool
    {
        $carry = false;

        foreach ($this->expressions as $expression) {
            if (
                ($carry && $expression->operator === Operator::Or) ||
                (! $carry && $expression->operator === Operator::And) ||
                (! $carry && $expression->operator === Operator::Not)
            ) {
                continue;
            }

            $matches = $expression->matches($scopes);

            match ($expression->operator) {
                Operator::None => $carry = $matches,
                Operator::And => $carry = $carry && $matches,
                Operator::Or => $carry = $carry || $matches,
                Operator::Not => $carry = $carry && ! $matches,
            };
        }

        return $carry;
    }
}
