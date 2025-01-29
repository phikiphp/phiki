<?php

namespace Phiki\CommonMark;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;

class CodeBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private string|array|Theme $theme,
        private Phiki $phiki = new Phiki,
        private bool $withGutter = false,
        private bool $withWrapper = false,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of '.FencedCode::class);
        }

        $code = rtrim($node->getLiteral(), "\n");
        $grammar = $this->detectGrammar($node, $code);

        return $this->phiki->codeToHtml($code, $grammar, $this->theme, $this->withGutter, $this->withWrapper);
    }

    protected function detectGrammar(FencedCode $node, string $code): Grammar|string
    {
        if (! isset($node->getInfoWords()[0]) || $node->getInfoWords()[0] === '') {
            return $this->phiki->detectGrammar($code) ?? 'txt';
        }

        preg_match('/[a-zA-Z]+/', $node->getInfoWords()[0], $matches);

        return $matches[0];
    }
}
