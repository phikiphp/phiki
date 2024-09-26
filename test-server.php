<?php

use Phiki\Phiki;

require_once __DIR__ . '/vendor/autoload.php';

echo Phiki::default()->codeToHtml(
    <<<'BLADE'
    <h1>{{ $hello + 100 }}</h1>
    BLADE,
    'blade',
    'github-dark'
);