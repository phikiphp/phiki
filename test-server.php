<style>
    pre {
        padding: 10px;
    }

    code {
        font-family: 'Fira Code';
    }
</style>
<?php

use Phiki\Phiki;

require_once __DIR__.'/vendor/autoload.php';

echo Phiki::default()->codeToHtml(
    <<<'BLADE'
    <h1>{{ $test }}</h1>
    BLADE,
    'blade',
    'github-dark'
);
