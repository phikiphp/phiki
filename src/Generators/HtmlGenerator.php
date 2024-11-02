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
    ) {}

    public function generate(array $tokens): string
    {
        $html = [];

        $html[] = sprintf(
            '<div class="phiki-wrapper" style="%s"%s>',
            $this->theme->base()->toStyleString(),
            $this->grammarName ? sprintf(' data-language="%s"', $this->grammarName) : '',
        );

        $html[] = sprintf(
            '<pre class="%s" style="%s"%s>',
            sprintf('phiki %s%s', $this->theme->name, $this->grammarName ? ' language-' . $this->grammarName : ''),
            $this->theme->base()->toStyleString(),
            $this->grammarName ? sprintf(' data-language="%s"', $this->grammarName) : '',
        );

        $html[] = '<code>';

        foreach ($tokens as $i => $line) {
            $html[] = sprintf(
                '<span class="line" data-line="%d">',
                $i + 1,
            );

            foreach ($line as $token) {
                $html[] = sprintf(
                    '<span class="token" style="%s">%s</span>',
                    $token->settings?->toStyleString(),
                    htmlspecialchars($token->token->text),
                );
            }

            $html[] = '</span>';
        }

        $html[] = '</code>';
        $html[] = '</pre>';
        $html[] = '</div>';

        return implode('', $html);
    }
}
