<?php

namespace Phiki;

use Phiki\Exceptions\MissingRequiredGrammarKeyException;
use Phiki\Grammar\BeginEndPattern;
use Phiki\Grammar\Capture;
use Phiki\Grammar\CollectionPattern;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\IncludePattern;
use Phiki\Grammar\MatchPattern;
use Phiki\Grammar\Pattern;

class GrammarParser
{
    protected string $scopeName;

    public function parse(array $grammar): Grammar
    {
        if (! isset($grammar['scopeName'])) {
            throw MissingRequiredGrammarKeyException::make('scopeName');
        }

        $this->scopeName = $scopeName = $grammar['scopeName'];
        $patterns = $this->patterns($grammar['patterns']);

        foreach ($grammar['repository'] as $name => $pattern) {
            $repository[$name] = $this->pattern($pattern);
        }

        return new Grammar($scopeName, $patterns, $repository);
    }

    protected function pattern(array $pattern): Pattern
    {
        if (isset($pattern['match'])) {
            return new MatchPattern(
                $pattern['match'],
                $pattern['name'] ?? null,
                $this->captures($pattern['captures'] ?? []),
            );
        }

        if (isset($pattern['begin'], $pattern['end'])) {
            return new BeginEndPattern(
                $pattern['begin'],
                $pattern['end'],
                $pattern['name'] ?? null,
                $pattern['contentName'] ?? null,
                $this->captures($pattern['beginCaptures'] ?? []),
                $this->captures($pattern['endCaptures'] ?? []),
                $this->captures($pattern['captures'] ?? []),
                $this->patterns($pattern['patterns'] ?? []),
            );
        }

        if (isset($pattern['include'])) {
            if (str_starts_with($pattern['include'], '#')) {
                [$reference, $scopeName] = [substr($pattern['include'], 1), $this->scopeName];
            } elseif ($pattern['include'] === '$self') {
                [$reference, $scopeName] = ['$self', $this->scopeName];
            } elseif ($pattern['include'] === '$base') {
                [$reference, $scopeName] = ['$base', null];
            } elseif (str_contains($pattern['include'], '#')) {
                [$scopeName, $reference] = explode('#', $pattern['include']);
            } else {
                [$reference, $scopeName] = [null, $pattern['include']];
            }

            return new IncludePattern($reference, $scopeName);
        }

        if (isset($pattern['patterns'])) {
            return new CollectionPattern($this->patterns($pattern['patterns']));
        }
    }

    protected function patterns(array $patterns): array
    {
        $result = [];

        foreach ($patterns as $pattern) {
            $result[] = $this->pattern($pattern);
        }

        return $result;
    }

    protected function captures(array $captures): array
    {
        $result = [];

        foreach ($captures as $index => $capture) {
            $result[$index] = $this->capture($capture, $index);
        }

        return $result;
    }

    protected function capture(array $capture, string $index): Capture
    {
        return new Capture($index, $capture['name'] ?? null, $this->patterns($capture['patterns'] ?? []));
    }
}