<?php

namespace Phiki\Transformers;

use Phiki\Contracts\TransformerInterface;
use Phiki\Phast\Element;
use Phiki\Phast\Root;
use Phiki\Token\HighlightedToken;
use Phiki\Token\Token;

class AbstractTransformer implements TransformerInterface
{
    /**
     * The meta information.
     */
    protected Meta $meta;

    /**
     * Modify the code before it is tokenized.
     */
    public function preprocess(string $code): string
    {
        return $code;
    }

    /**
     * Modify the tokens before they are highlighted.
     *
     * @param  array<int, array<Token>>  $tokens
     */
    public function tokens(array $tokens): array
    {
        return $tokens;
    }

    /**
     * Modify the highlighted tokens before they are converted into the HTML AST.
     *
     * @param  array<int, array<HighlightedToken>>  $tokens
     */
    public function highlighted(array $tokens): array
    {
        return $tokens;
    }

    /**
     * Modify the root HTML element.
     */
    public function root(Root $root): Root
    {
        return $root;
    }

    /**
     * Modify the <pre> tag.
     */
    public function pre(Element $pre): Element
    {
        return $pre;
    }

    /**
     * Modify the <code> tag.
     */
    public function code(Element $code): Element
    {
        return $code;
    }

    /**
     * Modify the <span> for each line.
     *
     * @param  array<int, HighlightedToken>  $tokens
     */
    public function line(Element $span, array $tokens, int $index): Element
    {
        return $span;
    }

    /**
     * Modify the <span> for each token.
     */
    public function token(Element $span, HighlightedToken $token, int $index, int $line): Element
    {
        return $span;
    }

    /**
     * Modify the <span> for line number.
     */
    public function gutter(Element $span, int $lineNumber): Element
    {
        return $span;
    }

    /**
     * Modify the HTML output after the AST has been converted.
     */
    public function postprocess(string $html): string
    {
        return $html;
    }

    /**
     * Store the meta object.
     */
    public function withMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }
}
