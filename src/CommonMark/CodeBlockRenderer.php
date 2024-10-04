<?php

namespace Phiki\CommonMark;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Phiki\Phiki;

class CodeBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private string $theme,
        private Phiki $phiki = new Phiki(),
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $grammar = $node->getInfoWords()[0];
        $code = rtrim($node->getLiteral(), "\n");

        return $this->phiki->codeToHtml($code, $grammar, $this->theme);
    }
}