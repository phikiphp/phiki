<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;

require_once __DIR__ . '/../vendor/autoload.php';

foreach (Grammar::cases() as $grammar) {
    $sampleFile = realpath(__DIR__ . "/../tests/Fixtures/samples/{$grammar->value}.sample");

    if (! file_exists($sampleFile)) {
        echo "No sample file found for grammar: {$grammar->value}\n";
        continue;
    }

    echo "Generating tokens for grammar: {$grammar->value}\n";

    shell_exec('node ' . realpath(__DIR__ . '/generate-vscode-textmate-tokens.js') . ' ' . escapeshellarg($sampleFile) . ' ' . escapeshellarg($grammar->scopeName()) . ' --file > ' . escapeshellarg(__DIR__ . "/../tests/Fixtures/vscode-tokens/{$grammar->value}.json"));
}

echo "vscode-textmate token generation complete.\n";

echo "Generating Phiki tokens for all grammars...\n";

$phiki = new Phiki;

foreach (Grammar::cases() as $grammar) {
    $sampleFile = realpath(__DIR__ . "/../tests/Fixtures/samples/{$grammar->value}.sample");

    if (! file_exists($sampleFile)) {
        echo "No sample file found for grammar: {$grammar->value}\n";
        continue;
    }

    echo "Generating Phiki tokens for grammar: {$grammar->value}\n";

    $sampleContent = file_get_contents($sampleFile);
    $tokens = $phiki->codeToTokens($sampleContent, $grammar);

    file_put_contents(__DIR__ . "/../tests/Fixtures/phiki-tokens/{$grammar->value}.json", json_encode($tokens, JSON_PRETTY_PRINT));
}
