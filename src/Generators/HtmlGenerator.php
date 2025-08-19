<?php

namespace Phiki\Generators;

use Phiki\Phast\ClassList;
use Phiki\Phast\Element;
use Phiki\Phast\Root;
use Phiki\Phast\Text;
use Phiki\Support\Arr;
use Phiki\Theme\ParsedTheme;
use Phiki\Token\HighlightedToken;

class HtmlGenerator
{
    /**
     * @param  array<string, ParsedTheme>  $themes
     */
    public function __construct(
        protected ?string $grammarName,
        protected array $themes,
        protected bool $withGutter = false,
    ) {}

    /**
     * @param array<array<int, HighlightedToken>> $tokens
     */
    public function generate(array $tokens): Root
    {
        $root = new Root([
            $pre = new Element('pre')
        ]);

        $pre->properties->set('class', $preClasses = (new ClassList)
            ->add(
                'phiki',
                $this->grammarName ? "language-$this->grammarName" : '',
                $this->getDefaultTheme()->name,
                count($this->themes) > 1 ? 'phiki-themes' : ''
            ));

        foreach ($this->themes as $theme) {
            if ($theme !== $this->getDefaultTheme()) {
                $preClasses->add($theme->name);
            }
        }

        $preStyles = [$this->getDefaultTheme()->base()->toStyleString()];

        foreach ($this->themes as $id => $theme) {
            if ($id !== $this->getDefaultThemeId()) {
                $preStyles[] = $theme->base()->toCssVarString($id);
            }
        }

        if ($this->grammarName) {
            $pre->properties->set('data-language', $this->grammarName);
        }
        
        $pre->properties->set('style', implode(';', $preStyles));

        $pre->children[] = $code = new Element('code');

        foreach ($tokens as $index => $lineTokens) {
            $code->children[] = $line = new Element('span');

            $line->properties->set('class', new ClassList(['line']));

            if ($this->withGutter) {
                $line->children[] = $gutter = new Element('span');

                $gutter->properties->set('class', new ClassList(['line-number']));

                $lineNumberColor = $this->getDefaultTheme()->colors['editorLineNumber.foreground'] ?? null;

                $gutter->properties->set('style', implode(';', array_filter([
                    $lineNumberColor ? "color: $lineNumberColor" : null,
                    '-webkit-user-select: none',
                ])));

                $gutter->children[] = new Text(sprintf('%2d', $index + 1));
            }

            foreach ($lineTokens as $token) {
                $line->children[] = $span = new Element('span');

                $tokenStyles = [($token->settings[$this->getDefaultThemeId()] ?? null)?->toStyleString()];

                foreach ($token->settings as $id => $settings) {
                    if ($id !== $this->getDefaultThemeId()) {
                        $tokenStyles[] = $settings->toCssVarString($id);
                    }
                }

                $span->properties->set('class', new ClassList(['token']));
                $span->properties->set('style', implode(';', array_filter($tokenStyles)));
                $span->children[] = new Text(htmlspecialchars($token->token->text));
            }
        }

        return $root;
    }

    private function getDefaultTheme(): ParsedTheme
    {
        return Arr::first($this->themes);
    }

    private function getDefaultThemeId(): string
    {
        return Arr::firstKey($this->themes);
    }
}
