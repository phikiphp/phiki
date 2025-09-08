<?php

namespace Phiki\Adapters\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<int, array<int, \Phiki\Token\Token>> codeToTokens(string $code, string|\Phiki\Grammar\Grammar|\Phiki\Grammar\ParsedGrammar $grammar)
 * @method static array<int, array<int, \Phiki\Token\HighlightedToken>> tokensToHighlightedTokens(array<int, array<int, \Phiki\Token\Token>> $tokens, string|array|\Phiki\Theme\Theme $theme)
 * @method static array<int, array<int, \Phiki\Token\HighlightedToken>> codeToHighlightedTokens(string $code, string|\Phiki\Grammar\Grammar $grammar, string|array|\Phiki\Theme\Theme $theme)
 * @method static \Phiki\Output\Html\PendingHtmlOutput codeToHtml(string $code, string|\Phiki\Grammar\Grammar $grammar, string|array|\Phiki\Theme\Theme $theme)
 * @method static \Phiki\Environment environment()
 * @method static \Phiki\Phiki extend(\Phiki\Contracts\ExtensionInterface $extension)
 * @method static \Phiki\Phiki grammar(string $name, string|\Phiki\Grammar\ParsedGrammar $pathOrGrammar)
 * @method static \Phiki\Phiki alias(string $alias, string | \Phiki\Grammar\Grammar $for)
 * @method static \Phiki\Phiki theme(string $name, string|\Phiki\Theme\ParsedTheme $pathOrTheme)
 * @method static \Phiki\Phiki cache(\Psr\SimpleCache\CacheInterface $cache)
 *
 * @see \Phiki\Phiki
 */
class Phiki extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Phiki\Phiki::class;
    }
}
