<?php

namespace Phiki\Transformers;

use Phiki\Contracts\TransformerInterface;
use Phiki\Html\Code;
use Phiki\Html\Element;
use Phiki\Html\Pre;
use Phiki\Html\Root;
use Phiki\Html\Span;

final class ProxyTransformer implements TransformerInterface
{
    /**
     * @param  \Phiki\Contracts\TransformerInterface[]  $transformers
     */
    public function __construct(
        private array $transformers = []
    ) {}

    public function preprocess(string $code): string
    {
        foreach ($this->transformers as $transformer) {
            $code = $transformer->preprocess($code);
        }

        return $code;
    }

    public function tokens(array $tokens): array
    {
        foreach ($this->transformers as $transformer) {
            $tokens = $transformer->tokens($tokens);
        }

        return $tokens;
    }

    public function root(Root $root): Root
    {
        foreach ($this->transformers as $transformer) {
            $root = $transformer->root($root);
        }

        return $root;
    }

    public function pre(Pre $pre): Pre
    {
        foreach ($this->transformers as $transformer) {
            $pre = $transformer->pre($pre);
        }

        return $pre;
    }

    public function code(Code $code): Code
    {
        foreach ($this->transformers as $transformer) {
            $code = $transformer->code($code);
        }

        return $code;
    }

    public function line(Element $element, int $line): Span|Element|null
    {
        foreach ($this->transformers as $transformer) {
            $element = $transformer->line($element, $line);
        }

        return $element;
    }

    public function token(Element $element): Span|Element|null
    {
        foreach ($this->transformers as $transformer) {
            $element = $transformer->token($element);
        }

        return $element;
    }

    public function postprocess(string $html): string
    {
        foreach ($this->transformers as $transformer) {
            $html = $transformer->postprocess($html);
        }

        return $html;
    }
}
