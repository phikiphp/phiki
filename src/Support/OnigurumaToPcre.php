<?php

namespace Phiki\Support;

/**
 * Translates Oniguruma regular expressions, as written in TextMate grammars, into
 * PCRE patterns with the same behaviour when compiled with the `u` and `m` flags.
 *
 * Most syntax is shared, so the translator only rewrites the constructs that differ:
 *
 * - `\h` / `\H` are hex digits in Oniguruma, but horizontal whitespace in PCRE.
 * - `(?m)` turns on dotall in Oniguruma, which PCRE spells `(?s)`.
 * - Nested character classes and class intersections (`[a-z&&[^aeiou]]`).
 * - The absent operator `(?~...)`.
 * - `\uHHHH` escapes and Unicode property names PCRE does not know.
 * - `{,n}` quantifiers and `{n,m}+`, which is a repeat, not a possessive quantifier.
 * - `\Q`, which is a literal "Q" rather than the start of a quoted sequence.
 */
class OnigurumaToPcre
{
    /**
     * Oniguruma property names that PCRE does not support, as class contents.
     */
    protected const PROPERTIES = [
        'alpha' => '\p{L}\p{Nl}\p{Mn}\p{Mc}',
        'alphabetic' => '\p{L}\p{Nl}\p{Mn}\p{Mc}',
        'alnum' => '\p{L}\p{Nl}\p{Mn}\p{Mc}\p{Nd}',
        'word' => '\w\p{M}',
        'blank' => '\t\p{Zs}',
        'space' => '\s',
        'digit' => '\p{Nd}',
        'upper' => '\p{Lu}',
        'lower' => '\p{Ll}',
        'punct' => '\p{P}',
        'print' => '[:print:]',
        'graph' => '[:graph:]',
        'cntrl' => '\p{Cc}',
        'xdigit' => '0-9A-Fa-f',
        'ascii' => '\x00-\x7F',
        'letter' => '\p{L}',
        'number' => '\p{N}',
        'mark' => '\p{M}',
        'punctuation' => '\p{P}',
        'symbol' => '\p{S}',
        'separator' => '\p{Z}',
        'other' => '\p{C}',
        'id_start' => '\p{L}\p{Nl}',
        'xid_start' => '\p{L}\p{Nl}',
        'id_continue' => '\p{L}\p{Nl}\p{Mn}\p{Mc}\p{Nd}\p{Pc}',
        'xid_continue' => '\p{L}\p{Nl}\p{Mn}\p{Mc}\p{Nd}\p{Pc}',
    ];

    /**
     * Escaped letters that Oniguruma treats as literals but PCRE rejects.
     */
    protected const UNKNOWN_ESCAPES = 'FIJLMOTUYijlmqy';

    /**
     * Escapes that are valid outside a class in PCRE, but which Oniguruma treats as literals inside one.
     */
    protected const UNKNOWN_CLASS_ESCAPES = 'BNRX';

    protected string $pattern;

    protected int $length;

    protected int $pos = 0;

    /**
     * Translate the given Oniguruma pattern into a PCRE pattern body (without delimiters).
     */
    public static function translate(string $pattern): string
    {
        return self::rewriteLookbehinds((new self($pattern))->translatePattern());
    }

    /**
     * The most alternatives a lookbehind is expanded into.
     */
    protected const MAX_LOOKBEHIND_ALTERNATIVES = 256;

    /**
     * Rewrite bounded variable-length lookbehinds, such as `(?<=(?:a|bc)\s?)`, as lists of
     * fixed-length alternatives. Oniguruma accepts them, but PCRE2 before 10.43 only accepts
     * alternatives of different lengths at the top level of a lookbehind.
     *
     * Lookbehinds with unbounded quantifiers cannot be expanded and are left unchanged.
     */
    protected static function rewriteLookbehinds(string $pattern): string
    {
        $length = strlen($pattern);
        $out = '';
        $i = 0;

        while ($i < $length) {
            $char = $pattern[$i];

            if ($char === '\\') {
                $end = self::escapeEnd($pattern, $i);
                $out .= substr($pattern, $i, $end - $i);
                $i = $end;

                continue;
            }

            if ($char === '[') {
                $end = self::classEnd($pattern, $i);
                $out .= substr($pattern, $i, $end - $i);
                $i = $end;

                continue;
            }

            if ($char === '(' && (substr($pattern, $i, 4) === '(?<=' || substr($pattern, $i, 4) === '(?<!')) {
                $end = self::groupEnd($pattern, $i);
                $body = substr($pattern, $i + 4, $end - $i - 5);
                $negative = $pattern[$i + 3] === '!';
                $alternatives = self::expandAlternation($body);

                if ($alternatives === null || count($alternatives) <= count(self::splitAlternation($body))) {
                    $out .= substr($pattern, $i, $end - $i);
                } elseif (self::countCaptures($body) === 0) {
                    $out .= '(?<'.($negative ? '!' : '=').implode('|', $alternatives).')';
                } else {
                    // A branch reset group keeps capture numbering the same in every alternative.
                    $lookbehinds = '(?|'.implode('|', array_map(fn (string $alternative) => '(?<='.$alternative.')', $alternatives)).')';
                    $out .= $negative ? '(?!'.$lookbehinds.')' : $lookbehinds;
                }

                $i = $end;

                continue;
            }

            $out .= $char;
            $i++;
        }

        return $out;
    }

    /**
     * Expand a PCRE pattern into the list of patterns without alternation inside groups or
     * bounded quantifiers that together match the same text, or null when that is impossible.
     *
     * @return list<string>|null
     */
    protected static function expandAlternation(string $pattern): ?array
    {
        $expanded = [];

        foreach (self::splitAlternation($pattern) as $alternative) {
            $options = self::expandSequence($alternative);

            if ($options === null) {
                return null;
            }

            array_push($expanded, ...$options);

            if (count($expanded) > self::MAX_LOOKBEHIND_ALTERNATIVES) {
                return null;
            }
        }

        return $expanded;
    }

    /**
     * @return list<string>|null
     */
    protected static function expandSequence(string $sequence): ?array
    {
        $results = [''];
        $length = strlen($sequence);
        $i = 0;

        while ($i < $length) {
            $char = $sequence[$i];
            $captures = 0;

            if ($char === '\\') {
                $end = self::escapeEnd($sequence, $i);
                $atom = substr($sequence, $i, $end - $i);

                // Back-references and `\R` / `\X` have no fixed length.
                if (preg_match('/^\\\\(?:[1-9]|[gk]|[RX])/', $atom)) {
                    return null;
                }

                $options = [$atom];
            } elseif ($char === '[') {
                $end = self::classEnd($sequence, $i);
                $options = [substr($sequence, $i, $end - $i)];
            } elseif ($char === '(') {
                $end = self::groupEnd($sequence, $i);
                $group = substr($sequence, $i, $end - $i);

                if (! preg_match('/^\((\?(?:[:>]|<[A-Za-z_]\w*>|P<[A-Za-z_]\w*>|\'[A-Za-z_]\w*\'|[a-zA-Z-]+:))?/', $group, $prefix) || str_starts_with($group, '(?|')) {
                    return null;
                }

                $opening = $prefix[0];

                if ($opening === '(' && $group[1] === '?') {
                    // Lookarounds, inline options and comments are zero-width: keep them as they are.
                    $options = [$group];
                } else {
                    $inner = self::expandAlternation(substr($group, strlen($opening), -1));

                    if ($inner === null) {
                        return null;
                    }

                    $options = array_map(fn (string $option) => $opening.$option.')', $inner);
                    $captures = self::countCaptures($group);
                }
            } else {
                // Copy one whole UTF-8 character.
                $end = $i + max(1, strlen(mb_substr(substr($sequence, $i, 4), 0, 1)));
                $options = [substr($sequence, $i, $end - $i)];
            }

            $i = $end;

            // Apply a following quantifier.
            if ($i < $length && preg_match('/\G(?:([?*+])|\{(\d+)(,?)(\d*)\})[?+]?/', $sequence, $q, 0, $i)) {
                $i += strlen($q[0]);

                if ($q[1] === '*' || $q[1] === '+' || ($q[1] === '' && $q[3] === ',' && $q[4] === '')) {
                    return null;
                }

                [$min, $max] = $q[1] === '?' ? [0, 1] : [(int) $q[2], $q[3] === '' ? (int) $q[2] : (int) $q[4]];
                $repeated = [];

                for ($count = $min; $count <= $max; $count++) {
                    if ($count === 0) {
                        // Keep capture numbering intact with empty groups.
                        $repeated[] = str_repeat('()', $captures);
                    } elseif (count($options) === 1) {
                        $repeated[] = $count === 1 ? $options[0] : '(?:'.$options[0].'){'.$count.'}';
                    } elseif ($captures > 0) {
                        return null;
                    } else {
                        $copies = [''];

                        for ($n = 0; $n < $count; $n++) {
                            $copies = self::product($copies, $options);

                            if ($copies === null) {
                                return null;
                            }
                        }

                        array_push($repeated, ...$copies);
                    }
                }

                $options = $repeated;
            }

            $results = self::product($results, $options);

            if ($results === null) {
                return null;
            }
        }

        return $results;
    }

    /**
     * @param  list<string>  $left
     * @param  list<string>  $right
     * @return list<string>|null
     */
    protected static function product(array $left, array $right): ?array
    {
        if (count($left) * count($right) > self::MAX_LOOKBEHIND_ALTERNATIVES) {
            return null;
        }

        $result = [];

        foreach ($left as $a) {
            foreach ($right as $b) {
                $result[] = $a.$b;
            }
        }

        return $result;
    }

    /**
     * Split a PCRE pattern on its top-level `|`.
     *
     * @return list<string>
     */
    protected static function splitAlternation(string $pattern): array
    {
        $parts = [];
        $start = 0;
        $length = strlen($pattern);
        $i = 0;

        while ($i < $length) {
            $char = $pattern[$i];

            $i = match ($char) {
                '\\' => self::escapeEnd($pattern, $i),
                '[' => self::classEnd($pattern, $i),
                '(' => self::groupEnd($pattern, $i),
                default => $i + 1,
            };

            if ($char === '|') {
                $parts[] = substr($pattern, $start, $i - 1 - $start);
                $start = $i;
            }
        }

        $parts[] = substr($pattern, $start);

        return $parts;
    }

    protected static function countCaptures(string $pattern): int
    {
        // Escapes and classes never contain groups, so drop them before counting.
        $pattern = preg_replace('/\\\\.|\[(?:\\\\.|[^\]])*\]/s', '', $pattern);

        return (int) preg_match_all('/\((?!\?)|\(\?(?:P?<[A-Za-z_]|\')/', $pattern);
    }

    /**
     * Find the end of the escape sequence starting at the given position.
     */
    protected static function escapeEnd(string $pattern, int $i): int
    {
        if (preg_match('/\G\\\\(?:[xopPgk]\{[^}]*\}|[gk]<[^>]*>|x[0-9a-fA-F]{0,2}|c.|[pP].|.)/s', $pattern, $m, 0, $i)) {
            return $i + strlen($m[0]);
        }

        return $i + 1;
    }

    /**
     * Find the end of the PCRE character class starting at the given position.
     */
    protected static function classEnd(string $pattern, int $i): int
    {
        $length = strlen($pattern);
        $i++;

        if (($pattern[$i] ?? '') === '^') {
            $i++;
        }

        if (($pattern[$i] ?? '') === ']') {
            $i++;
        }

        while ($i < $length) {
            if ($pattern[$i] === '\\') {
                $i = self::escapeEnd($pattern, $i);

                continue;
            }

            if ($pattern[$i] === '[' && preg_match('/\G\[:\^?[a-zA-Z]+:\]/', $pattern, $m, 0, $i)) {
                $i += strlen($m[0]);

                continue;
            }

            if ($pattern[$i] === ']') {
                return $i + 1;
            }

            $i++;
        }

        return $length;
    }

    /**
     * Find the end of the group starting at the given position.
     */
    protected static function groupEnd(string $pattern, int $i): int
    {
        $length = strlen($pattern);
        $depth = 0;

        while ($i < $length) {
            $char = $pattern[$i];

            if ($char === '\\') {
                $i = self::escapeEnd($pattern, $i);

                continue;
            }

            if ($char === '[') {
                $i = self::classEnd($pattern, $i);

                continue;
            }

            $i++;

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')' && --$depth === 0) {
                return $i;
            }
        }

        return $length;
    }

    protected function __construct(string $pattern)
    {
        $this->pattern = $pattern;
        $this->length = strlen($pattern);
    }

    protected function translatePattern(): string
    {
        $out = '';
        // Each open group remembers whether extended mode is on inside it, what to emit
        // when it closes, and where it started in the output so a following `{n,m}+`
        // can wrap it.
        $groups = [];
        $extended = false;
        $atomStart = 0;

        while ($this->pos < $this->length) {
            $char = $this->pattern[$this->pos];

            if ($char === '\\') {
                $atomStart = strlen($out);
                $out .= $this->translateEscape();

                continue;
            }

            if ($char === '[') {
                $atomStart = strlen($out);
                $out .= $this->emitClass($this->parseClass());

                continue;
            }

            if ($char === '(') {
                if (substr($this->pattern, $this->pos, 3) === '(?#') {
                    $end = strpos($this->pattern, ')', $this->pos);
                    $end = $end === false ? $this->length : $end + 1;
                    $out .= substr($this->pattern, $this->pos, $end - $this->pos);
                    $this->pos = $end;

                    continue;
                }

                if (preg_match('/\G\(\?([imx]*)(-[imx]*)?([:)])/', $this->pattern, $m, PREG_UNMATCHED_AS_NULL, $this->pos) && ($m[1] !== '' || $m[2] !== null)) {
                    $on = $m[1];
                    $off = $m[2] ?? '';
                    $flagsExtended = str_contains($on, 'x') ? true : (str_contains($off, 'x') ? false : $extended);
                    $flags = strtr($on.$off, 'm', 's');
                    $this->pos += strlen($m[0]);

                    // Oniguruma applies `(?i)` to the rest of the enclosing group, including any
                    // later alternatives, so `a(?i)b|c` means `a(?i:b|c)`. PCRE would read it as
                    // `(?:a(?i)b)|c`, so the rest of the group is wrapped in an implicit group.
                    $groups[] = [$extended, ')', strlen($out), $m[3] === ')'];
                    $extended = $flagsExtended;
                    $out .= '(?'.$flags.':';

                    continue;
                }

                if (substr($this->pattern, $this->pos, 3) === '(?~') {
                    // Absent operator: matches any text that does not contain the inner pattern.
                    $groups[] = [$extended, ')[\s\S])*)', strlen($out)];
                    $out .= '(?:(?:(?!';
                    $this->pos += 3;

                    continue;
                }

                $groups[] = [$extended, ')', strlen($out)];
                $out .= '(';
                $this->pos++;

                continue;
            }

            if ($char === ')') {
                $this->pos++;

                while ($groups !== [] && (end($groups)[3] ?? false)) {
                    [$extended] = array_pop($groups);
                    $out .= ')';
                }

                if ($groups === []) {
                    $out .= ')';

                    continue;
                }

                [$extended, $close, $atomStart] = array_pop($groups);
                $out .= $close;

                continue;
            }

            if ($char === '{' && preg_match('/\G\{(\d*)(,?)(\d*)\}(\+?)/', $this->pattern, $m, 0, $this->pos) && ($m[1] !== '' || ($m[2] !== '' && $m[3] !== ''))) {
                $this->pos += strlen($m[0]);
                $quantifier = '{'.($m[1] === '' ? '0' : $m[1]).$m[2].$m[3].'}';

                if ($m[4] === '+') {
                    // Oniguruma reads `a{2}+` as `(?:a{2})+`, where PCRE would make it possessive.
                    $out = substr($out, 0, $atomStart).'(?:'.substr($out, $atomStart).$quantifier.')+';
                } else {
                    $out .= $quantifier;
                }

                continue;
            }

            if ($extended && $char === '#') {
                $end = strpos($this->pattern, "\n", $this->pos);
                $end = $end === false ? $this->length : $end;
                $out .= substr($this->pattern, $this->pos, $end - $this->pos);
                $this->pos = $end;

                continue;
            }

            if (! ($extended && ctype_space($char)) && ! in_array($char, ['*', '+', '?', '|'], true)) {
                $atomStart = strlen($out);
            }

            $out .= $char;
            $this->pos++;
        }

        foreach (array_reverse($groups) as $group) {
            if ($group[3] ?? false) {
                // A trailing extended-mode comment would otherwise swallow the closing parenthesis.
                $out .= ($extended ? "\n" : '').')';
                $extended = $group[0];
            }
        }

        return $out;
    }

    /**
     * Translate an escape sequence outside of a character class.
     */
    protected function translateEscape(): string
    {
        $next = $this->pattern[$this->pos + 1] ?? '';

        switch ($next) {
            case 'h':
                $this->pos += 2;

                return '[0-9a-fA-F]';
            case 'H':
                $this->pos += 2;

                return '[^0-9a-fA-F]';
            case 'Q':
            case 'E':
                $this->pos += 2;

                return $next;
            case 'u':
                if (preg_match('/\G\\\\u([0-9a-fA-F]{4})/', $this->pattern, $m, 0, $this->pos)) {
                    $this->pos += 6;

                    return '\x{'.$m[1].'}';
                }

                break;
            case 'p':
            case 'P':
                $property = $this->parseProperty();

                if ($property !== null) {
                    [$negated, $contents] = $property;

                    return $this->isSingleItem($contents)
                        ? ($negated ? $this->negateItem($contents) : $contents)
                        : '['.($negated ? '^' : '').$contents.']';
                }

                break;
        }

        $this->pos += 2;

        // Oniguruma reads an escaped letter it does not know as the letter itself, where PCRE fails to compile.
        if (str_contains(self::UNKNOWN_ESCAPES, $next)) {
            return $next;
        }

        return '\\'.$next;
    }

    /**
     * Parse a `\p{...}` property at the current position into its negation and PCRE class contents.
     *
     * @return array{0: bool, 1: string}|null
     */
    protected function parseProperty(): ?array
    {
        if (! preg_match('/\G\\\\([pP])\{(\^?)([^}]+)\}/', $this->pattern, $m, 0, $this->pos)) {
            return null;
        }

        $this->pos += strlen($m[0]);
        $negated = ($m[1] === 'P') !== ($m[2] === '^');
        $name = strtolower(str_replace([' ', '-'], ['', '_'], $m[3]));

        return [$negated, self::PROPERTIES[$name] ?? '\p{'.$m[3].'}'];
    }

    protected function isSingleItem(string $contents): bool
    {
        return (bool) preg_match('/^\\\\[pPdDsSwW](\{[^}]+\})?$/', $contents);
    }

    protected function negateItem(string $item): string
    {
        return $item[0].(ctype_lower($item[1]) ? strtoupper($item[1]) : strtolower($item[1])).substr($item, 2);
    }

    /**
     * Parse a character class starting at the current `[`.
     *
     * A class is an intersection of one or more unions. Each union holds PCRE class
     * contents for the simple members and nested class nodes for the rest.
     *
     * @return array{negated: bool, unions: list<array{contents: string, nested: list<array<string, mixed>>}>}
     */
    protected function parseClass(): array
    {
        $this->pos++;
        $negated = false;

        if (($this->pattern[$this->pos] ?? '') === '^') {
            $negated = true;
            $this->pos++;
        }

        $unions = [];
        $contents = '';
        $nested = [];
        $first = true;

        while ($this->pos < $this->length) {
            $char = $this->pattern[$this->pos];

            if ($char === ']' && ! $first) {
                $this->pos++;

                break;
            }

            $first = false;

            if ($char === ']') {
                $contents .= '\]';
                $this->pos++;

                continue;
            }

            if ($char === '&' && ($this->pattern[$this->pos + 1] ?? '') === '&') {
                $unions[] = ['contents' => $contents, 'nested' => $nested];
                $contents = '';
                $nested = [];
                $this->pos += 2;

                continue;
            }

            if ($char === '[') {
                if (preg_match('/\G\[:\^?[a-zA-Z]+:\]/', $this->pattern, $m, 0, $this->pos)) {
                    $contents .= $m[0];
                    $this->pos += strlen($m[0]);

                    continue;
                }

                $nested[] = $this->parseClass();

                continue;
            }

            if ($char === '\\') {
                $next = $this->pattern[$this->pos + 1] ?? '';

                if ($next === 'h') {
                    $contents .= '0-9a-fA-F';
                    $this->pos += 2;

                    continue;
                }

                if ($next === 'H') {
                    $nested[] = ['negated' => true, 'unions' => [['contents' => '0-9a-fA-F', 'nested' => []]]];
                    $this->pos += 2;

                    continue;
                }

                if ($next === 'u' && preg_match('/\G\\\\u([0-9a-fA-F]{4})/', $this->pattern, $m, 0, $this->pos)) {
                    $contents .= '\x{'.$m[1].'}';
                    $this->pos += 6;

                    continue;
                }

                if (($next === 'p' || $next === 'P') && ($property = $this->parseProperty()) !== null) {
                    [$propertyNegated, $propertyContents] = $property;

                    if (! $propertyNegated) {
                        $contents .= $propertyContents;
                    } elseif ($this->isSingleItem($propertyContents)) {
                        $contents .= $this->negateItem($propertyContents);
                    } else {
                        $nested[] = ['negated' => true, 'unions' => [['contents' => $propertyContents, 'nested' => []]]];
                    }

                    continue;
                }

                if ($next === 'p' || $next === 'P' || ($next !== '' && str_contains(self::UNKNOWN_CLASS_ESCAPES.self::UNKNOWN_ESCAPES, $next))) {
                    $contents .= $next;
                    $this->pos += 2;

                    continue;
                }

                $contents .= substr($this->pattern, $this->pos, 2);
                $this->pos += 2;

                continue;
            }

            // A leading `:`, `.` or `=` would make PCRE read the class as a POSIX class or collating element.
            if ($contents === '' && $nested === [] && in_array($char, [':', '.', '='], true)) {
                $contents .= '\\'.$char;
                $this->pos++;

                continue;
            }

            $contents .= $char;
            $this->pos++;
        }

        $unions[] = ['contents' => $contents, 'nested' => $nested];

        return ['negated' => $negated, 'unions' => $unions];
    }

    /**
     * Emit a parsed character class as PCRE.
     *
     * @param  array{negated: bool, unions: list<array{contents: string, nested: list<array<string, mixed>>}>}  $class
     */
    protected function emitClass(array $class): string
    {
        // The common case: a single union whose nested classes are all plain, positive
        // sets, which can be flattened into one PCRE class.
        if (count($class['unions']) === 1) {
            $flattened = $this->flatten($class['unions'][0]);

            if ($flattened !== null) {
                return '['.($class['negated'] ? '^' : '').$flattened.']';
            }
        }

        $parts = array_map(fn (array $union) => $this->emitUnion($union), $class['unions']);
        $last = array_shift($parts);
        $expression = '(?:'.implode('', array_map(fn (string $part) => '(?='.$part.')', $parts)).$last.')';

        return $class['negated'] ? '(?:(?!'.$expression.')[\s\S])' : $expression;
    }

    /**
     * @param  array{contents: string, nested: list<array<string, mixed>>}  $union
     */
    protected function flatten(array $union): ?string
    {
        $contents = $union['contents'];

        foreach ($union['nested'] as $nested) {
            if ($nested['negated'] || count($nested['unions']) !== 1) {
                return null;
            }

            $inner = $this->flatten($nested['unions'][0]);

            if ($inner === null) {
                return null;
            }

            // A `-` at the edge of a nested class is literal in Oniguruma, but would form a range once flattened.
            if (str_ends_with($contents, '-') && ! str_ends_with($contents, '\\-')) {
                $contents = substr($contents, 0, -1).'\\-';
            }

            if (str_starts_with($inner, '-')) {
                $inner = '\\'.$inner;
            }

            if (str_ends_with($inner, '-') && ! str_ends_with($inner, '\\-')) {
                $inner = substr($inner, 0, -1).'\\-';
            }

            $contents .= $inner;
        }

        return $contents;
    }

    /**
     * @param  array{contents: string, nested: list<array<string, mixed>>}  $union
     */
    protected function emitUnion(array $union): string
    {
        $flattened = $this->flatten($union);

        if ($flattened !== null) {
            return '['.$flattened.']';
        }

        $alternatives = $union['contents'] === '' ? [] : ['['.$union['contents'].']'];

        foreach ($union['nested'] as $nested) {
            $alternatives[] = $this->emitClass($nested);
        }

        return '(?:'.implode('|', $alternatives).')';
    }
}
