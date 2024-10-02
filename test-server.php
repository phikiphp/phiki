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

dd(Phiki::default()->codeToTokens(
    <<<'BLADE'
    @php
    @endphp
    BLADE,
    'blade',
    'github-dark'
));
