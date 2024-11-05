<?php

namespace Phiki\Generators;

use Phiki\Contracts\OutputGeneratorInterface;
use Phiki\Support\Arr;
use Phiki\Theme\ParsedTheme;

class HtmlGenerator implements OutputGeneratorInterface
{
    /**
     * @param  array<string, ParsedTheme>  $themes
     */
    public function __construct(
        protected ?string $grammarName,
        protected array $themes,
    ) {}

    public function generate(array $tokens): string
    {
        $html = [];
        $defaultTheme = Arr::first($this->themes);
        $defaultThemeId = Arr::firstKey($this->themes);

        $wrapperStyles = [
            $defaultTheme->base()->toStyleString(),
        ];

        foreach ($this->themes as $id => $theme) {
            if ($id === $defaultThemeId) {
                continue;
            }

            $wrapperStyles[] = $theme->base()->toCssVarString($id);
        }

        $html[] = sprintf(
            '<div class="phiki-wrapper" style="%s"%s>',
            implode(';', $wrapperStyles),
            $this->grammarName ? sprintf(' data-language="%s"', $this->grammarName) : '',
        );

        $preClasses = ['phiki', $this->grammarName ? 'language-'.$this->grammarName : null, $defaultTheme->name];

        if (count($this->themes) > 1) {
            $preClasses[] = 'phiki-themes';

            foreach ($this->themes as $theme) {
                if ($theme === $defaultTheme) {
                    continue;
                }

                $preClasses[] = $theme->name;
            }
        }

        $preStyles = [
            $defaultTheme->base()->toStyleString(),
        ];

        foreach ($this->themes as $id => $theme) {
            if ($id === $defaultThemeId) {
                continue;
            }

            $preStyles[] = $theme->base()->toCssVarString($id);
        }

        $html[] = sprintf(
            '<pre class="%s" style="%s"%s>',
            implode(' ', array_filter($preClasses)),
            implode(';', $preStyles),
            $this->grammarName ? sprintf(' data-language="%s"', $this->grammarName) : '',
        );

        $html[] = '<code>';

        foreach ($tokens as $i => $line) {
            $html[] = sprintf(
                '<span class="line" data-line="%d">',
                $i + 1,
            );

            foreach ($line as $token) {
                if ($token->settings === []) {
                    $html[] = sprintf(
                        '<span class="token">%s</span>',
                        htmlspecialchars($token->token->text),
                    );

                    continue;
                }

                $tokenStyles = [
                    $token->settings[$defaultThemeId]->toStyleString(),
                ];

                foreach ($token->settings as $id => $settings) {
                    if ($settings === $token->settings[$defaultThemeId]) {
                        continue;
                    }

                    $tokenStyles[] = $settings->toCssVarString($id);
                }

                $html[] = sprintf(
                    '<span class="token" style="%s">%s</span>',
                    implode(';', $tokenStyles),
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
