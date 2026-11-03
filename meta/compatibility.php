<?php

/**
 * Compare how the Oniguruma and PCRE engines tokenize the sample for every grammar, and write
 * the results to COMPATIBILITY.md. Oniguruma is the reference, so this needs mbregex or mb_onig.
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
 * Key each token on a line by its position and scopes. The newline at the end of each line is
 * left out: mbregex lets `.` match it and PCRE does not, which never changes the rendered output.
 *
 * @param  Token[]  $line
 * @return list<string>
 */
function tokenKeys(array $line): array
{
    $keys = [];

    foreach ($line as $token) {
        $text = $token === end($line) ? rtrim($token->text, "\n") : $token->text;

        if ($text !== '') {
            $keys[] = $token->start.':'.($token->start + strlen($text)).':'.implode(' ', $token->scopes);
        }
    }

    return $keys;
}

/**
 * @return array{tokens: int, matching: int, lines: int, identicalLines: int, firstDifference: ?int, coverage: float}
 */
function compareGrammar(Grammar $grammar, string $code): array
{
    $recorder = new RecordingEngine(new OnigurumaEngine);
    $oniguruma = (new Phiki)->engine($recorder);
    $expected = $oniguruma->codeToTokens($code, $grammar);
    $actual = (new Phiki)->engine(new PcreEngine)->codeToTokens($code, $grammar);

    $rules = grammarRegexes($oniguruma->environment->grammars->get($grammar->value));
    $result = ['tokens' => 0, 'matching' => 0, 'lines' => count($expected), 'identicalLines' => 0, 'firstDifference' => null];

    foreach ($expected as $index => $line) {
        $expectedKeys = tokenKeys($line);
        $actualKeys = tokenKeys($actual[$index] ?? []);

        $result['tokens'] += count($expectedKeys);
        $result['matching'] += count(array_intersect($expectedKeys, $actualKeys));

        if ($expectedKeys === $actualKeys) {
            $result['identicalLines']++;
        } else {
            $result['firstDifference'] ??= $index + 1;
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
    fwrite(STDERR, "The Oniguruma engine is the reference for this report, so it needs mbregex or the mb_onig extension.\n");

    exit(1);
}

$only = array_slice($argv, 1);
$rows = [];
$totals = ['tokens' => 0, 'matching' => 0, 'lines' => 0, 'identicalLines' => 0, 'identical' => 0];

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

    $result = compareGrammar($grammar, file_get_contents(__DIR__."/../{$sample}"));
    $identical = $result['firstDifference'] === null;

    foreach (['tokens', 'matching', 'lines', 'identicalLines'] as $key) {
        $totals[$key] += $result[$key];
    }

    $totals['identical'] += (int) $identical;

    $rows[$grammar->value] = sprintf(
        '| %s | [%s](%s) | %s | %d | %s | %s | %s |',
        $grammar->value,
        basename($sample),
        $sample,
        percent($result['coverage'], 1),
        $result['tokens'],
        percent($result['matching'], $result['tokens']),
        percent($result['identicalLines'], $result['lines']),
        $identical ? '✅ Identical' : sprintf('[Line %d](%s#L%d)', $result['firstDifference'], $sample, $result['firstDifference']),
    );
}

ksort($rows);

$header = [
    '| Grammar | Sample | Rule coverage | Tokens | Matching tokens | Identical lines | First difference |',
    '| --- | --- | ---: | ---: | ---: | ---: | --- |',
];

if ($only !== []) {
    echo implode("\n", [...$header, ...$rows]), "\n";

    exit;
}

$report = <<<'MD'
# Engine compatibility

Phiki can tokenize code with two regex engines: Oniguruma (through mbregex or the `mb_onig` extension), and PCRE, which every PHP build includes. Oniguruma is the engine TextMate grammars are written for, so it is the reference. This report shows how closely the PCRE engine reproduces its tokens for the sample of each bundled grammar.

Regenerate it with `composer compatibility` after changing the PCRE engine, the translator, the grammars or the samples.

- **Rule coverage** is the share of the grammar's own rules that match at least once while its sample is tokenized. Rules from embedded grammars are not counted.
- **Matching tokens** is the share of Oniguruma's tokens that PCRE produces with the same position, text and scopes.
- **Identical lines** is the share of lines whose tokens are all the same.
- The newline at the end of each line is not compared. mbregex lets `.` match it and PCRE does not, which never changes the rendered output.

Generated with PHP %s, PCRE2 %s and Oniguruma %s.

## Summary

- %d of %d grammars tokenize their sample identically.
- %s of tokens match (%d of %d).
- %s of lines are identical (%d of %d).

## Grammars


MD;

$report = sprintf(
    $report,
    PHP_VERSION,
    explode(' ', PCRE_VERSION)[0],
    defined('MB_ONIGURUMA_VERSION') ? MB_ONIGURUMA_VERSION : 'unknown',
    $totals['identical'],
    count($rows),
    percent($totals['matching'], $totals['tokens']),
    $totals['matching'],
    $totals['tokens'],
    percent($totals['identicalLines'], $totals['lines']),
    $totals['identicalLines'],
    $totals['lines'],
);

file_put_contents(__DIR__.'/../COMPATIBILITY.md', $report.implode("\n", [...$header, ...$rows])."\n");

echo "Wrote COMPATIBILITY.md: {$totals['identical']} of ".count($rows)." grammars identical.\n";
