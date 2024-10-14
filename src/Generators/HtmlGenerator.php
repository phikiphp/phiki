<?php

namespace Phiki\Generators;

use Phiki\Contracts\OutputGeneratorInterface;
use Phiki\Html\AttributeList;
use Phiki\Html\Code;
use Phiki\Html\Pre;
use Phiki\Html\Root;
use Phiki\Html\Span;
use Phiki\Html\Text;
use Phiki\Theme\ParsedTheme;
use Phiki\Transformers\ProxyTransformer;

class HtmlGenerator implements OutputGeneratorInterface
{
    public function __construct(
        protected ?string $grammarName,
        protected ParsedTheme $theme,
        protected ProxyTransformer $proxy,
    ) {}

    public function generate(array $tokens): string
    {
        $lines = [];

        foreach ($tokens as $i => $line) {
            $children = [];

            foreach ($line as $token) {
                $children[] = $this->proxy->token(new Span(
                    new AttributeList([
                        'class' => 'token',
                        'style' => $token->settings?->toStyleString(),
                    ]),
                    [new Text($token->token->text)],
                ));
            }

            $lines[] = $this->proxy->line(new Span(
                new AttributeList([
                    'class' => 'line',
                ]),
                $children,
            ), $i);
        }

        $code = $this->proxy->code(new Code(children: $lines));

        $pre = $this->proxy->pre(new Pre($code, new AttributeList([
            'class' => sprintf('phiki %s%s', $this->theme->name, $this->grammarName ? ' language-'.$this->grammarName : ''),
            'style' => $this->theme->base()->toStyleString(),
            'data-language' => $this->grammarName,
        ])));

        $root = $this->proxy->root(new Root($pre, new AttributeList([
            'class' => 'phiki-wrapper',
            'data-language' => $this->grammarName,
        ])));

        return $this->proxy->postprocess($root->__toString());
    }
}
