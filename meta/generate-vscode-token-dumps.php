<?php

use Phiki\Grammar\Grammar;

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

echo "Token generation complete.\n";
