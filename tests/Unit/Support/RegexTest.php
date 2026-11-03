<?php

use Phiki\Support\Regex;

/**
 * Patterns are written in Oniguruma syntax, as they appear in TextMate grammars. Each expected
 * result is what Oniguruma (via vscode-oniguruma) matches for the same pattern and subject.
 */
function matchRegex(string $pattern, string $subject, ?array $references = null): ?array
{
    $result = preg_match((new Regex($pattern))->get(true, true, $references), $subject, $matches);

    expect($result)->not->toBeFalse();

    return $result === 1 ? $matches : null;
}

it('translates Oniguruma syntax to PCRE', function (string $pattern, string $subject, ?string $expected) {
    expect(matchRegex($pattern, $subject)[0] ?? null)->toBe($expected);
})->with([
    'hex digit escape' => ['\h+', 'x af9', 'af9'],
    'inline flag applies to later alternatives' => ['a(?i)b|c', 'c', null],
    'inline flag still applies after it' => ['a(?i)b|c', 'aB', 'aB'],
    'dot does not match newlines' => ['a.b', "a\nb", null],
    '(?m) is dotall' => ['(?m)a.b', "a\nb", "a\nb"],
    'quantifier without a minimum' => ['a{,2}', 'aaa', 'aa'],
    'interval followed by + repeats' => ['a{2}+', 'aaaaa', 'aaaa'],
    'class intersection' => ['[a-z&&[^aeiou]]+', 'abc', 'bc'],
    'nested class starting with a dash' => ['[^\s[-?:]]+', 'a-b c', 'a'],
    'Blank property' => ['\p{Blank}+', "x \ty", " \t"],
    'absent operator' => ['(?~ab)', 'xxabyy', 'xx'],
    '\Q is a literal' => ['\Qa.b\E', 'Qa.bE', 'Qa.bE'],
    'POSIX bracket outside a class is a plain class' => ['[:alpha:]+', 'pha:', 'pha:'],
    'unknown escape in a class is a literal' => ['[\p]+', 'ppq', 'pp'],
    'Unicode word characters' => ['\w+', 'naïve!', 'naïve'],
    'Unicode POSIX classes' => ['[[:alpha:]]+', 'é1', 'é'],
    'back-reference in a match pattern' => ['([\'"]).*?\1', "x'a'", "'a'"],
    'lookbehind with nested alternatives of different lengths' => ['(?<=(?:x|yy))b', 'yyb', 'b'],
]);

it('keeps captures in rewritten lookbehinds', function () {
    expect(matchRegex('(?<=(a|bc))d', 'bcd'))->toBe(['d', 'bc']);
});

it('replaces back-references with escaped captures from the begin pattern', function () {
    expect(matchRegex('\1', 'a.b a+b', [['a+b', 0], ['a+b', 0]])[0] ?? null)->toBe('a+b');
});
