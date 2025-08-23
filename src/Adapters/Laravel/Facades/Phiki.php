<?php

namespace Phiki\Adapters\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Phiki\Environment\Environment environment()
 * @method static array<int, array<int, \Phiki\Token\Token>> codeToTokens(string $code, string|\Phiki\Grammar\Grammar|\Phiki\Grammar\ParsedGrammar $grammar)
 * @method static array<int, array<int, \Phiki\Token\HighlightedToken>> tokensToHighlightedTokens(array<int, array<int, \Phiki\Token\Token>> $tokens, string|array|\Phiki\Theme\Theme $theme)
 * @method static array<int, array<int, \Phiki\Token\HighlightedToken>> codeToHighlightedTokens(string $code, string|\Phiki\Grammar\Grammar $grammar, string|array|\Phiki\Theme\Theme $theme)
 * @method static \Phiki\Output\Html\PendingHtmlOutput codeToHtml(string $code, string|\Phiki\Grammar\Grammar $grammar, string|array|\Phiki\Theme\Theme $theme)
 * @method static \Phiki\Phiki addExtension(\Phiki\Contracts\ExtensionInterface $extension)
 * @method static \Phiki\Phiki registerGrammar(string $name, string|\Phiki\Grammar\ParsedGrammar $pathOrGrammar)
 * @method static \Phiki\Phiki registerTheme(string $name, string|\Phiki\Theme\ParsedTheme $pathOrTheme)
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
