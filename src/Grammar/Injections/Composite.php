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
            // $carry already determines the outcome for this operator, so we can skip
            // evaluating the expression entirely.
            if (
                ($carry && $expression->operator === Operator::Or) ||
                (! $carry && $expression->operator === Operator::And) ||
                (! $carry && $expression->operator === Operator::Not)
            ) {
                continue;
            }

            $matches = $expression->matches($scopes);

            // Getting past the short-circuit above tells us what $carry must be: true for
            // And and Not, false for Or. That makes it redundant in the boolean algebra,
            // so the outcome only depends on $matches, negated for Not.
            $carry = $expression->operator === Operator::Not ? ! $matches : $matches;
        }

        return $carry;
    }
}
