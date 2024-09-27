<?php

namespace Phiki\Contracts;

use Phiki\Grammar\Grammar;

interface GrammarRepositoryInterface
{
    /**
     * Get a grammar from the repository.
     *
     * If the grammar is not already loaded, it will be loaded and cached.
     *
     * @param  string  $name  The name of the grammar.
     *
     * @throws \Phiki\Exceptions\UnrecognisedGrammarException If the grammar is not registered.
     */
    public function get(string $name): Grammar;

    /**
     * Get a grammar from the repository by scope name.
     *
     * @param  string  $scope  The name of the scope.
     *
     * @throws \Phiki\Exceptions\UnrecognisedGrammarException If the grammar is not registered.
     */
    public function getFromScope(string $scope): Grammar;

    /**
     * Check whether a grammar exists in the repository.
     *
     * @param  string  $name  The name of the grammar.
     */
    public function has(string $name): bool;

    /**
     * Register a new Grammar to use when highlighting.
     *
     * @param  string  $name  The name of the grammar.
     * @param  string|Grammar  $pathOrGrammar  The path to the grammar file or the grammar itself.
     */
    public function register(string $name, string|Grammar $pathOrGrammar): void;
}
