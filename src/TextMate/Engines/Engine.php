<?php

namespace Phiki\TextMate\Engines;

/**
 * The regex engines Phiki supports. Each value names the directory in `resources/grammars`
 * that holds the bundled grammars written for that engine.
 */
enum Engine: string
{
    case Oniguruma = 'oniguruma';
    case Pcre = 'pcre';
}
