<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Support\Regex;

require_once __DIR__ . '/vendor/autoload.php';

$regex = new Regex("(^\s+)?(?=//)");

mb_ereg_search_init("// This is a comment.", "(^\s+)?(?=//)");
mb_ereg_search_setpos(0);

// dd(mb_ereg_search_pos());

dd($regex->match("// This is a comment.", $matches, 0, false, false));

// $tokens = (new Phiki)
//     ->codeToTokens(
//         <<<'PHP'
//         // Hello, world!
//         PHP,
//         Grammar::Php,
//     );
