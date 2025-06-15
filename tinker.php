<?php

use Phiki\Grammar\Grammar;

require_once __DIR__ . '/vendor/autoload.php';

$files = array_map(fn (Grammar $grammar) => realpath($grammar->path()), Grammar::cases());
$real = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/resources/languages', FilesystemIterator::SKIP_DOTS));

foreach ($real as $file) {
    $path = $file->getRealpath();

    if (! in_array($path, $files, true)) {
        unlink($path);
    }
}

