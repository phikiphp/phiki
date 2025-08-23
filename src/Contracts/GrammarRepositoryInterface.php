<?php

namespace Phiki\Contracts;

use Phiki\Grammar\Grammar;
use Phiki\Grammar\ParsedGrammar;

interface GrammarRepositoryInterface
{
    /**
     * Get a grammar from the repository.
     *
     * @throws \Phiki\Exceptions\UnrecognisedGrammarException If the grammar is not registered.
     */
    public function get(string $name): ParsedGrammar;

    /**
     * Get a grammar from the repository by scope name.
     *
     * @throws \Phiki\Exceptions\UnrecognisedGrammarException If the grammar is not registered.
     */
    public function getFromScope(string $scope): ParsedGrammar;

    /**
     * Check whether a grammar exists in the repository.
     */
    public function has(string $name): bool;

    /**
     * Register a new grammar to use when highlighting.
     */
    public function register(string $name, string|ParsedGrammar $pathOrGrammar): void;

    /**
     * Resolve the given grammar from the repository.
     */
    public function resolve(string|Grammar|ParsedGrammar $theme): ParsedGrammar;
}
