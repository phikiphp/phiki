<?php

namespace Phiki\Transformers;

use Phiki\Contracts\TransformerInterface;
use Phiki\Phast\Element;
use Phiki\Phast\Root;
use Phiki\Token\Token;
use Phiki\Token\HighlightedToken;

class AbstractTransformer implements TransformerInterface
{
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
     * @param array<int, array<Token>> $tokens
     */
    public function tokens(array $tokens): array
    {
        return $tokens;
    }

    /**
     * Modify the highlighted tokens before they are converted into the HTML AST.
     * 
     * @param array<int, array<HighlightedToken>> $tokens
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
     */
    public function line(Element $line): Element
    {
        return $line;
    }

    /**
     * Modify the <span> for each token.
     */
    public function token(Element $token): Element
    {
        return $token;
    }

    /**
     * Modify the HTML output after the AST has been converted.
     */
    public function postprocess(string $html): string
    {
        return $html;
    }
}
