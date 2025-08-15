<?php

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Support\Regex;
use Phiki\Support\Str;

require_once __DIR__ . '/vendor/autoload.php';

preg_match(Str::CAPTURING_REGEX_SOURCE, 'entity.name.$1.test', $matches);

dd($matches);
