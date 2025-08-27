<?php

namespace Phiki\Adapters\CommonMark;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Phiki\Adapters\CommonMark\Transformers\AnnotationsTransformer;
use Phiki\Adapters\CommonMark\Transformers\MetaTransformer;
use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Phiki\Transformers\Meta;

class CodeBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private string|array|Theme $theme,
        private Phiki $phiki = new Phiki,
        private bool $withGutter = false,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of '.FencedCode::class);
        }

        $code = rtrim($node->getLiteral(), "\n");
        $grammar = $this->detectGrammar($node);
        $meta = new Meta(markdownInfo: $node->getInfoWords()[1] ?? null);

        return $this->phiki->codeToHtml($code, $grammar, $this->theme)
            ->withGutter($this->withGutter)
            ->withMeta($meta)
            ->transformer(new MetaTransformer)
            ->transformer(new AnnotationsTransformer)
            ->toString();
    }

    protected function detectGrammar(FencedCode $node): Grammar|string
    {
        if (! isset($node->getInfoWords()[0]) || $node->getInfoWords()[0] === '') {
            return Grammar::Txt;
        }

        preg_match('/[a-zA-Z]+/', $node->getInfoWords()[0], $matches);

        if (! $this->phiki->environment->grammars->has($matches[0])) {
            return Grammar::Txt;
        }

        return $matches[0];
    }
}
