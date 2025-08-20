<?php

namespace Phiki\Tests\Fixtures;

use Phiki\Contracts\TransformerInterface;
use Phiki\Phast\Root;
use Phiki\Phast\Element;

class UselessTransformer implements TransformerInterface
{
    public $preprocessed = false;

    public $tokens = false;

    public $highlighted = false;

    public $root = false;

    public $pre = false;

    public $code = false;

    public $line = false;

    public $token = false;

    public $postprocessed = false;

    public function preprocess(string $code): string
    {
        $this->preprocessed = true;

        return $code;
    }

    public function tokens(array $tokens): array
    {
        $this->tokens = true;

        return $tokens;
    }

    public function highlighted(array $tokens): array
    {
        $this->highlighted = true;

        return $tokens;
    }

    public function root(Root $root): Root
    {
        $this->root = true;

        return $root;
    }

    public function pre(Element $pre): Element
    {
        $this->pre = true;

        return $pre;
    }

    public function code(Element $code): Element
    {
        $this->code = true;

        return $code;
    }

    public function line(Element $line): Element
    {
        $this->line = true;

        return $line;
    }

    public function token(Element $token): Element
    {
        $this->token = true;

        return $token;
    }

    public function postprocess(string $html): string
    {
        $this->postprocessed = true;
        
        return $html;
    }
}
