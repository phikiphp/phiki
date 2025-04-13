<?php

namespace Phiki\CommonMark;

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\CodeRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Phiki\Phiki;
use Phiki\Theme\Theme;

class InlineCodeRenderer implements NodeRendererInterface
{
    public function __construct(
        private string|array|Theme $theme,
        private Phiki $phiki = new Phiki,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Code) {
            throw new \InvalidArgumentException('Node must be instance of '.Code::class);
        }

        $internal = new CodeRenderer();

        preg_match('/^\{(?<match>[\w]+)}(?<code>.*)/', $node->getLiteral(), $match);

        if (! isset($match['match'])) {
            return $internal->render($node, $childRenderer);
        }

        $grammar = $match['match'] ?? 'txt';
        $code = $match['code'] ?? $node->getLiteral();

        return $this->phiki->codeToHtml($code, $grammar, $this->theme, inline: true);
    }
}
