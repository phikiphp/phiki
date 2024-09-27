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
    <<<'PHP'
    <<<'HTML'
    <h1>Testing</h1>
    HTML;
    PHP,
    'php',
    'github-dark'
);
