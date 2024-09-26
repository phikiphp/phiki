<?php

namespace Phiki\Contracts;

interface GrammarRepositoryInterface
{
    /**
     * Get a grammar from the repository.
     * 
     * If the grammar is not already loaded, it will be loaded and cached.
     * 
     * @param string $name The name of the grammar.
     * @return array
     * @throws \Phiki\Exceptions\UnrecognisedGrammarException If the grammar is not registered.
     */
    public function get(string $name): array;

    /**
     * Check whether a grammar exists in the repository.
     * 
     * @param string $name The name of the grammar.
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Register a new Grammar to use when highlighting.
     * 
     * @param string $name The name of the grammar.
     * @param string|array $pathOrGrammar The path to the grammar file or the grammar itself.
     * @return void
     */
    public function register(string $name, string|array $pathOrGrammar): void;
}