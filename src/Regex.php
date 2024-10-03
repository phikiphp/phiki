<?php

namespace Phiki;

/**
 * This class is responsible for converting an Oniguruma pattern into a PCRE2/PHP compatible pattern.
 */
class Regex
{
    public function __construct(
        protected string $pattern,
    ) {}    

    public function get(): string
    {
        $pattern = preg_replace('/(?<!\\\)\//', '\\/', $this->pattern);

        return $pattern;
    }
}