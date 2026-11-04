<?php

/**
 * Time how long the Oniguruma and PCRE engines take to tokenize and highlight the sample for every
 * grammar, next to vscode-textmate's tokenization and Shiki's highlighting of the same sample, and
 * write the results to PERFORMANCE.md. This needs mbregex or mb_onig for the Oniguruma engine, and
 * Node.js with the npm dependencies for vscode-textmate and Shiki.
 *
 * Usage: php meta/performance.php [grammar ...]
 * Passing grammar names prints their results instead of writing the report.
 */

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\TextMate\Engines\OnigurumaEngine;
use Phiki\TextMate\Engines\PcreEngine;

require_once __DIR__.'/../vendor/autoload.php';

ini_set('memory_limit', '-1');

// Each time is the median of this many runs. PHP runs each sample once before timing it, to load the
// grammars. Node.js runs it RUNS_NODE times first, so V8 has also optimized the code.
const RUNS = 5;
const RUNS_NODE = 10;
const THEME = 'github-light';

/**
 * Run the callback once to load the grammars, then return the median time of the timed runs in milliseconds.
 */
function measure(callable $callback): float
{
    $callback();
    $times = [];

    for ($i = 0; $i < RUNS; $i++) {
        $start = hrtime(true);
        $callback();
        $times[] = (hrtime(true) - $start) / 1e6;
    }

    sort($times);

    return $times[intdiv(count($times), 2)];
}

function milliseconds(float $ms): string
{
    return $ms >= 100 ? sprintf('%.0f ms', $ms) : sprintf('%.1f ms', $ms);
}

/**
 * How many times slower than the reference, such as `2.4×`.
 */
function ratio(float $ms, float $reference): string
{
    return sprintf('%.1f×', $ms / $reference);
}

/**
 * The geometric mean of how many times slower each grammar is than the reference, so that the large
 * samples don't outweigh the rest.
 *
 * @param  array<string, array<string, float>>  $results
 */
function typicalRatio(array $results, string $key, string $reference): string
{
    $logs = array_map(fn (array $result) => log($result[$key] / $result[$reference]), $results);

    return sprintf('%.1f×', exp(array_sum($logs) / count($logs)));
}

if (! OnigurumaEngine::isSupported()) {
    fwrite(STDERR, "This report times the Oniguruma engine too, so it needs mbregex or the mb_onig extension.\n");

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

$referenceFile = tempnam(sys_get_temp_dir(), 'phiki-performance');
$command = implode(' ', array_map('escapeshellarg', ['node', __DIR__.'/performance.js', $referenceFile, THEME, RUNS_NODE, ...array_keys($samples)]));
exec($command, result_code: $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "Could not time vscode-textmate and Shiki. Run `npm ci` first.\n");

    exit(1);
}

$references = json_decode(file_get_contents($referenceFile), true);
unlink($referenceFile);

$engines = [
    'oniguruma' => (new Phiki)->engine(new OnigurumaEngine),
    'pcre' => (new Phiki)->engine(new PcreEngine),
];

$results = [];
$rows = [];

foreach ($samples as $name => [$grammar, $sample]) {
    fwrite(STDERR, "Timing {$name}\n");

    $code = file_get_contents(__DIR__."/../{$sample}");
    $result = [
        'lines' => count(preg_split("/\R/u", $code)),
        'vscode-textmate' => $references[$name]['tokenize'],
        'shiki' => $references[$name]['highlight'],
    ];

    foreach ($engines as $engine => $phiki) {
        $result["{$engine}-tokenize"] = measure(fn () => $phiki->codeToTokens($code, $grammar));
        $result["{$engine}-highlight"] = measure(fn () => $phiki->codeToHtml($code, $grammar, THEME)->toString());
    }

    $results[$name] = $result;
    $rows[$name] = sprintf(
        '| %s | [%s](%s) | %d | %s | %s | %s | %s | %s | %s |',
        $name,
        basename($sample),
        $sample,
        $result['lines'],
        milliseconds($result['oniguruma-tokenize']),
        milliseconds($result['pcre-tokenize']),
        milliseconds($result['vscode-textmate']),
        milliseconds($result['oniguruma-highlight']),
        milliseconds($result['pcre-highlight']),
        milliseconds($result['shiki']),
    );
}

ksort($rows);

$header = [
    '| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |',
    '| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |',
];

if ($only !== []) {
    echo implode("\n", [...$header, ...$rows]), "\n";

    exit;
}

$total = fn (string $key) => array_sum(array_column($results, $key));

$summary = [
    '| Task | Implementation | All samples | Compared with JavaScript | Typical sample compared with JavaScript |',
    '| --- | --- | ---: | ---: | ---: |',
];

foreach (['tokenize' => 'vscode-textmate', 'highlight' => 'shiki'] as $task => $reference) {
    $label = ucfirst($task);
    $summary[] = sprintf('| %s | %s | %s | – | – |', $label, $reference === 'shiki' ? 'Shiki' : 'vscode-textmate', milliseconds($total($reference)));

    foreach (['oniguruma' => 'Phiki, Oniguruma', 'pcre' => 'Phiki, PCRE'] as $engine => $implementation) {
        $summary[] = sprintf(
            '| %s | %s | %s | %s | %s |',
            $label,
            $implementation,
            milliseconds($total("{$engine}-{$task}")),
            ratio($total("{$engine}-{$task}"), $total($reference)),
            typicalRatio($results, "{$engine}-{$task}", $reference),
        );
    }
}

$node = trim((string) shell_exec('node --version'));
$runs = RUNS;
$runsNode = RUNS_NODE;
$theme = THEME;
$count = count($rows);
$summary = implode("\n", $summary);
$cpu = PHP_OS_FAMILY === 'Linux' && preg_match('/^model name\s*:\s*(.+)$/m', (string) @file_get_contents('/proc/cpuinfo'), $matches) ? $matches[1] : php_uname('m');
$opcache = function_exists('opcache_get_status') && opcache_get_status() !== false ? 'enabled' : 'disabled';
$jit = $opcache === 'enabled' && (opcache_get_status()['jit']['on'] ?? false) ? 'enabled' : 'disabled';

$report = <<<MD
# Performance

This report times how long Phiki takes to tokenize and highlight the sample of each bundled grammar with each of its regex engines, Oniguruma and PCRE. It compares tokenizing with [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki, and highlighting with [Shiki](https://shiki.style), using its Oniguruma engine.

Regenerate it with `composer performance` after changing an engine, the tokenizer, the highlighter or the HTML output. It needs Node.js and `npm ci`. Timings depend on the machine, so compare reports generated on the same one.

- **Tokenize** is `Phiki::codeToTokens()`, and a call to `tokenizeLine()` for each line in vscode-textmate.
- **Highlight** is `Phiki::codeToHtml()` with the `{$theme}` theme, rendered to a string, and `codeToHtml()` in Shiki.
- Every implementation uses Phiki's bundled grammars and theme, so they all work from the same input.
- Each time is the median of {$runs} runs in PHP and {$runsNode} in Node.js. Before timing, each sample is tokenized and highlighted once in PHP and {$runsNode} times in Node.js, so V8 has optimized the code. Loading and compiling the grammars is not counted.
- **Compared with JavaScript** is how many times longer Phiki takes than vscode-textmate or Shiki for all {$count} samples together. **Typical sample** is the geometric mean of that for each sample, so the largest samples don't outweigh the rest.

Generated with PHP %s (OPcache %s, JIT %s), PCRE2 %s, Oniguruma %s and Node.js {$node}, on %s.

## Summary

{$summary}

## Grammars


MD;

$report = sprintf(
    $report,
    PHP_VERSION,
    $opcache,
    $jit,
    explode(' ', PCRE_VERSION)[0],
    defined('MB_ONIGURUMA_VERSION') ? MB_ONIGURUMA_VERSION : 'unknown',
    $cpu,
);

file_put_contents(__DIR__.'/../PERFORMANCE.md', $report.implode("\n", [...$header, ...$rows])."\n");

echo "Wrote PERFORMANCE.md.\n";
