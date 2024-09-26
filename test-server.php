<?php

use Phiki\Phiki;

require_once __DIR__ . '/vendor/autoload.php';

echo Phiki::default()->codeToHtml(
    <<<'BLADE'
    <h1>Hello, world!</h1>
    BLADE,
    'html',
    'github-dark'
);