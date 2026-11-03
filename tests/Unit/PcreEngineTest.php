<?php

use Phiki\Exceptions\UnsupportedRegexEngineException;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\MatchPattern;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Phiki;
use Phiki\Support\OnigurumaToPcre;
use Phiki\Support\Regex;
use Phiki\TextMate\Engines\OnigurumaEngine;
use Phiki\TextMate\Engines\PcreEngine;

/**
 * Patterns are written in Oniguruma syntax, as they appear in TextMate grammars. Each expected
 * result is what Oniguruma (via vscode-oniguruma) matches for the same pattern and subject.
 */
function pcreMatch(string $pattern, string $subject): ?array
{
    $pcre = OnigurumaToPcre::translate($pattern);
    $matched = (new PcreEngine)->findNextMatch([[new MatchPattern(0, new Regex($pcre), null), $pcre]], $subject, 0);

    return $matched === null ? null : array_column($matched->matches, 0);
}

it('matches translated Oniguruma patterns', function (string $pattern, string $subject, ?string $expected) {
    expect(pcreMatch($pattern, $subject)[0] ?? null)->toBe($expected);
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
    'lookbehind with nested alternatives of different lengths' => ['(?<=(?:x|yy))b', 'yyb', 'b'],
    'interval followed by ? is optional' => ['a{2}?', 'ab', ''],
    'escape followed by interval and + repeats' => ['\x41{2}+', 'AAAAA', 'AAAA'],
    'lookbehind with an unbounded quantifier' => ['(?<=\.\s*)await', 'x .  await', 'await'],
]);

it('sets the captures of groups run by subexpression calls', function (string $pattern, string $subject, array $expected) {
    expect(pcreMatch($pattern, $subject))->toBe($expected);
})->with([
    'named call' => ['(\$)(?<name>[a-z]+)(?:(->)(\g<name>))?', '$post->title', ['$post->title', '$', 'title', '->', 'title']],
    'numbered call keeps later group numbers' => ['(a)\g<1>(b)', 'aab', ['aab', 'a', 'b']],
    'relative call' => ['(\[)(?:x|\g<-1>)*', '[[x', ['[[x', '[']],
    'recursive call' => ['(?<conid>[A-Z][a-z]*(\.\g<conid>)?)', 'Data.Maybe', ['Data.Maybe', 'Data.Maybe', '.Maybe']],
    'group name with a dash' => ['(?<type-name>[a-z]+)=\g<type-name>', 'ab=cd', ['ab=cd', 'cd']],
]);

it('keeps captures in rewritten lookbehinds', function () {
    expect(pcreMatch('(?<=(a|bc))d', 'bcd'))->toBe(['d', 'bc']);
});

it('reports byte offsets for captures on multibyte lines', function () {
    $matched = (new PcreEngine)->findNextMatch([[new MatchPattern(0, new Regex('(é)(x)?(b)'), null), '(é)(x)?(b)']], 'aéb', 0);

    expect($matched->matches)->toBe([['éb', 1], ['é', 1], ['', -1], ['b', 3]]);
});

describe('engine selection', function () {
    // mbregex lets `.` match the newline at the end of each line, and PCRE does not.
    $grammar = fn () => ParsedGrammar::fromArray(['scopeName' => 'source.test', 'patterns' => [['match' => '.*', 'name' => 'line.test']]]);

    it('uses Oniguruma by default when mbregex is available', function () use ($grammar) {
        expect((new Phiki)->codeToTokens('a', $grammar())[0][0]->text)->toBe("a\n");
    })->skip(! OnigurumaEngine::isSupported(), 'mbregex is not available');

    it('uses the engine it is configured with', function () use ($grammar) {
        expect((new Phiki)->engine(new PcreEngine)->codeToTokens('a', $grammar())[0][0]->text)->toBe('a');
    });

    it('loads the bundled grammars translated for the configured engine', function () {
        // The C grammar finds parameter names with a lookbehind that PCRE only compiles once translated.
        $tokens = (new Phiki)->engine(new PcreEngine)->codeToTokens('void qsort(int v[], int left);', Grammar::C)[0];
        $parameter = array_values(array_filter($tokens, fn ($token) => $token->text === 'v'))[0] ?? null;

        expect($parameter?->scopes)->toContain('variable.parameter.probably.c');
    });

    it('rejects the Oniguruma engine without mbregex', function () {
        expect(fn () => new OnigurumaEngine)->toThrow(UnsupportedRegexEngineException::class);
    })->skip(OnigurumaEngine::isSupported(), 'mbregex is available');
});
