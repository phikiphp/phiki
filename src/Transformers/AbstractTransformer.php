<?php

namespace Phiki\Transformers;

use Phiki\Contracts\TransformerInterface;
use Phiki\Html\Code;
use Phiki\Html\Element;
use Phiki\Html\Pre;
use Phiki\Html\Root;
use Phiki\Html\Span;

abstract class AbstractTransformer implements TransformerInterface
{
    public function preprocess(string $code): string
    {
        return $code;
    }

    public function tokens(array $tokens): array
    {
        return $tokens;
    }

    public function root(Root $root): Root
    {
        return $root;
    }

    public function pre(Pre $pre): Pre
    {
        return $pre;
    }

    public function code(Code $code): Code
    {
        return $code;
    }

    public function line(Span $span): Span | Element | null
    {
        return $span;
    }

    public function token(Span $span): Span | Element | null
    {
        return $span;
    }

    public function postprocess(string $html): string
    {
        return $html;
    }
}