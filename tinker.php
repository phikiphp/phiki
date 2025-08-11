<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;

require_once __DIR__ . '/vendor/autoload.php';

$phiki = new Phiki();

$tokens = $phiki->codeToTokens(
    <<<'EXAMPLE'
class Foo {

}
EXAMPLE,
    Grammar::Php,
);

dd($tokens);
