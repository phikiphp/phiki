<?php

/**
 * Compare how the Oniguruma and PCRE engines tokenize the sample for every grammar with how
 * vscode-textmate does, and write the results to COMPATIBILITY.md. This needs mbregex or mb_onig
 * for the Oniguruma engine, and Node.js with the npm dependencies for vscode-textmate.
 *
 * Usage: php meta/compatibility.php [grammar ...]
 * Passing grammar names prints their results instead of writing the report.
 */

use Phiki\Contracts\RegexEngineInterface;
use Phiki\Grammar\BeginEndPattern;
use Phiki\Grammar\BeginWhilePattern;
use Phiki\Grammar\EndPattern;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\MatchedPattern;
use Phiki\Grammar\MatchPattern;
use Phiki\Grammar\WhilePattern;
use Phiki\Phiki;
use Phiki\Support\Regex;
use Phiki\TextMate\Engines\Engine;
use Phiki\TextMate\Engines\OnigurumaEngine;
use Phiki\TextMate\Engines\PcreEngine;
use Phiki\Token\Token;

require_once __DIR__.'/../vendor/autoload.php';

ini_set('memory_limit', '-1');

/**
 * Records which grammar rules match, to measure how much of a grammar its sample exercises.
 */
final class RecordingEngine implements RegexEngineInterface
{
    /**
     * @var array<int, true>
     */
    public array $matched = [];

    public function __construct(private RegexEngineInterface $engine) {}

    public function identifier(): Engine
    {
        return $this->engine->identifier();
    }

    public function findNextMatch(array $patterns, string $lineText, int $linePos): ?MatchedPattern
    {
        $matched = $this->engine->findNextMatch($patterns, $lineText, $linePos);
        $pattern = $matched?->pattern;

        $regex = match (true) {
            $pattern instanceof MatchPattern => $pattern->match,
            $pattern instanceof BeginEndPattern, $pattern instanceof BeginWhilePattern => $pattern->begin,
            $pattern instanceof EndPattern => $pattern->end,
            $pattern instanceof WhilePattern => $pattern->while,
            default => null,
        };

        if ($regex !== null) {
            $this->matched[spl_object_id($regex)] = true;
        }

        return $matched;
    }
}

/**
 * Collect the IDs of every Regex in a parsed grammar. Included grammars are referenced by name,
 * so only the grammar's own rules are counted.
 *
 * @return array<int, true>
 */
function grammarRegexes(object $grammar): array
{
    $found = [];
    $seen = [];
    $stack = [$grammar];

    while ($stack !== []) {
        $node = array_pop($stack);

        if ($node instanceof Regex) {
            $found[spl_object_id($node)] = true;

            continue;
        }

        if (is_object($node)) {
            if (isset($seen[spl_object_id($node)])) {
                continue;
            }

            $seen[spl_object_id($node)] = true;
            $node = (array) $node;
        }

        foreach ($node as $value) {
            if (is_array($value) || is_object($value)) {
                $stack[] = $value;
            }
        }
    }

    return $found;
}

/**
 * Key each token on a line by its position in code points and its scopes, so they can be compared
 * with vscode-textmate's. Neighbouring tokens with the same scopes are merged, since where they are
 * split never changes the rendered output, and the newline at the end of the line is left out.
 *
 * @param  list<array{int, int, string}>  $tokens
 * @return list<string>
 */
function tokenKeys(array $tokens): array
{
    $merged = [];

    foreach ($tokens as [$start, $end, $scopes]) {
        if ($start === $end) {
            continue;
        }

        $last = array_key_last($merged);

        if ($last !== null && $merged[$last][2] === $scopes && $merged[$last][1] === $start) {
            $merged[$last][1] = $end;
        } else {
            $merged[] = [$start, $end, $scopes];
        }
    }

    return array_map(fn (array $token) => implode(':', $token), $merged);
}

/**
 * @param  Token[]  $line
 * @return list<array{int, int, string}>
 */
function phikiTokens(array $line): array
{
    $tokens = [];
    $offset = 0;

    foreach ($line as $token) {
        $length = mb_strlen(rtrim($token->text, "\n"));
        $tokens[] = [$offset, $offset + $length, implode(' ', $token->scopes)];
        $offset += $length;
    }

    return $tokens;
}

/**
 * Compare the tokens each engine produces for the sample with vscode-textmate's.
 *
 * @param  list<list<array{int, int, string}>>  $reference
 * @return array{tokens: int, oniguruma: int, pcre: int, onigurumaIdentical: bool, firstDifference: ?int, coverage: float}
 */
function compareGrammar(Grammar $grammar, string $code, array $reference): array
{
    $recorder = new RecordingEngine(new OnigurumaEngine);
    $oniguruma = (new Phiki)->engine($recorder);
    $engines = [
        'oniguruma' => $oniguruma->codeToTokens($code, $grammar),
        'pcre' => (new Phiki)->engine(new PcreEngine)->codeToTokens($code, $grammar),
    ];

    $rules = grammarRegexes($oniguruma->environment->grammars->get($grammar->value));
    $result = ['tokens' => 0, 'oniguruma' => 0, 'pcre' => 0, 'onigurumaIdentical' => true, 'firstDifference' => null];

    foreach ($reference as $index => $line) {
        $expected = tokenKeys($line);
        $result['tokens'] += count($expected);

        foreach ($engines as $engine => $lines) {
            $actual = tokenKeys(phikiTokens($lines[$index] ?? []));
            $result[$engine] += count(array_intersect($expected, $actual));

            if ($actual !== $expected) {
                $engine === 'pcre' ? $result['firstDifference'] ??= $index + 1 : $result['onigurumaIdentical'] = false;
            }
        }
    }

    $result['coverage'] = $rules === [] ? 1.0 : count(array_intersect_key($recorder->matched, $rules)) / count($rules);

    return $result;
}

function percent(int|float $part, int|float $whole): string
{
    return $whole == 0 ? '–' : sprintf('%.1f%%', 100 * $part / $whole);
}

if (! OnigurumaEngine::isSupported()) {
    fwrite(STDERR, "This report compares the Oniguruma engine too, so it needs mbregex or the mb_onig extension.\n");

    exit(1);
}

$only = array_slice($argv, 1);
$samples = [];

foreach (Grammar::cases() as $grammar) {
    // ANSI is handled by its own tokenizer rather than a TextMate grammar.
    if ($grammar === Grammar::Ansi || ($only !== [] && ! in_array($grammar->value, $only, true))) {
        continue;
    }

    $sample = "tests/Fixtures/samples/{$grammar->value}.sample";

    if (! is_file(__DIR__."/../{$sample}")) {
        fwrite(STDERR, "Missing sample: {$sample}\n");

        continue;
    }

    $samples[$grammar->value] = [$grammar, $sample];
}

// vscode-textmate is the reference: it is what VS Code and Shiki tokenize with.
$referenceFile = tempnam(sys_get_temp_dir(), 'phiki-compatibility');
$command = implode(' ', array_map('escapeshellarg', ['node', __DIR__.'/vscode-textmate-samples.js', $referenceFile, ...array_keys($samples)]));
exec($command, result_code: $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "Could not tokenize the samples with vscode-textmate. Run `npm ci` first.\n");

    exit(1);
}

$references = json_decode(file_get_contents($referenceFile), true);
unlink($referenceFile);

$rows = [];
$totals = ['tokens' => 0, 'oniguruma' => 0, 'pcre' => 0, 'onigurumaIdentical' => 0, 'pcreIdentical' => 0];

foreach ($samples as $name => [$grammar, $sample]) {
    $result = compareGrammar($grammar, file_get_contents(__DIR__."/../{$sample}"), $references[$name]);
    $identical = $result['firstDifference'] === null;

    foreach (['tokens', 'oniguruma', 'pcre'] as $key) {
        $totals[$key] += $result[$key];
    }

    $totals['onigurumaIdentical'] += (int) $result['onigurumaIdentical'];
    $totals['pcreIdentical'] += (int) $identical;

    $rows[$name] = sprintf(
        '| %s | [%s](%s) | %s | %d | %s | %s | %s |',
        $name,
        basename($sample),
        $sample,
        percent($result['coverage'], 1),
        $result['tokens'],
        percent($result['oniguruma'], $result['tokens']),
        percent($result['pcre'], $result['tokens']),
        $identical ? '✅ Identical' : sprintf('[Line %d](%s#L%d)', $result['firstDifference'], $sample, $result['firstDifference']),
    );
}

ksort($rows);

$header = [
    '| Grammar | Sample | Rule coverage | Tokens | Oniguruma | PCRE | First PCRE difference |',
    '| --- | --- | ---: | ---: | ---: | ---: | --- |',
];

if ($only !== []) {
    echo implode("\n", [...$header, ...$rows]), "\n";

    exit;
}

$report = <<<'MD'
# Engine compatibility

Phiki can tokenize code with two regex engines: Oniguruma (through mbregex or the `mb_onig` extension), and PCRE, which every PHP build includes. This report compares the tokens each engine produces for the sample of each bundled grammar with the tokens from [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki.

Regenerate it with `composer compatibility` after changing an engine, the translator, the tokenizer, the grammars or the samples. It needs Node.js and `npm ci`.

- **Rule coverage** is the share of the grammar's own rules that match at least once while its sample is tokenized. Rules from embedded grammars are not counted.
- **Oniguruma** and **PCRE** are the share of vscode-textmate's tokens that each engine produces with the same position and scopes.
- **First PCRE difference** is the first line where the PCRE engine's tokens differ from vscode-textmate's.
- Neighbouring tokens with the same scopes are merged before comparing, and the newline at the end of each line is not compared. Neither changes the rendered output.

Generated with PHP %s, PCRE2 %s and Oniguruma %s.

## Summary

| Engine | Grammars identical | Matching tokens |
| --- | ---: | ---: |
| Oniguruma | %d of %d | %s (%d of %d) |
| PCRE | %d of %d | %s (%d of %d) |

## Grammars


MD;

$report = sprintf(
    $report,
    PHP_VERSION,
    explode(' ', PCRE_VERSION)[0],
    defined('MB_ONIGURUMA_VERSION') ? MB_ONIGURUMA_VERSION : 'unknown',
    $totals['onigurumaIdentical'],
    count($rows),
    percent($totals['oniguruma'], $totals['tokens']),
    $totals['oniguruma'],
    $totals['tokens'],
    $totals['pcreIdentical'],
    count($rows),
    percent($totals['pcre'], $totals['tokens']),
    $totals['pcre'],
    $totals['tokens'],
);

file_put_contents(__DIR__.'/../COMPATIBILITY.md', $report.implode("\n", [...$header, ...$rows])."\n");

echo "Wrote COMPATIBILITY.md: PCRE matches vscode-textmate for {$totals['pcreIdentical']} of ".count($rows)." grammars, Oniguruma for {$totals['onigurumaIdentical']}.\n";
