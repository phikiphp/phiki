<?php

/**
 * Translate the bundled Oniguruma grammars into resources/grammars/pcre for the PCRE engine.
 *
 * Pass --check to fail instead of writing when the PCRE grammars are out of date.
 */

use Phiki\Support\OnigurumaToPcre;
use Phiki\TextMate\Engines\Engine;

require_once __DIR__.'/../vendor/autoload.php';

$check = in_array('--check', $argv, true);
$source = __DIR__.'/../resources/grammars/'.Engine::Oniguruma->value;
$target = __DIR__.'/../resources/grammars/'.Engine::Pcre->value;
$stale = [];

if (! $check && ! is_dir($target)) {
    mkdir($target, 0755, true);
}

foreach (glob("{$source}/*.json") as $file) {
    $grammar = OnigurumaToPcre::translateGrammar(json_decode(file_get_contents($file), false, flags: JSON_THROW_ON_ERROR));
    $json = json_encode($grammar, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $path = $target.'/'.basename($file);

    if (! $check) {
        file_put_contents($path, $json);
    } elseif (! is_file($path) || file_get_contents($path) !== $json) {
        $stale[] = basename($file);
    }
}

if ($stale !== []) {
    fwrite(STDERR, "These PCRE grammars are out of date. Run `composer translate-grammars`:\n  ".implode("\n  ", $stale)."\n");

    exit(1);
}

echo $check ? "PCRE grammars are up to date.\n" : "Translated grammars into resources/grammars/pcre.\n";
