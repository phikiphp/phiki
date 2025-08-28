<?php

namespace Phiki\Output\Html;

use Closure;
use Phiki\Contracts\RequiresGrammarInterface;
use Phiki\Contracts\RequiresThemesInterface;
use Phiki\Contracts\TransformerInterface;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Phast\ClassList;
use Phiki\Phast\Element;
use Phiki\Phast\Properties;
use Phiki\Phast\Root;
use Phiki\Phast\Text;
use Phiki\Support\Arr;
use Phiki\Theme\ParsedTheme;
use Phiki\Token\HighlightedToken;
use Phiki\Token\Token;
use Phiki\Transformers\Decorations\CodeDecoration;
use Phiki\Transformers\Decorations\DecorationTransformer;
use Phiki\Transformers\Decorations\GutterDecoration;
use Phiki\Transformers\Decorations\LineDecoration;
use Phiki\Transformers\Decorations\PreDecoration;
use Phiki\Transformers\Meta;
use Psr\SimpleCache\CacheInterface;
use Stringable;

class PendingHtmlOutput implements Stringable
{
    protected bool $withGutter = false;

    protected ?Closure $generateTokensUsing = null;

    protected ?Closure $highlightTokensUsing = null;

    protected ?CacheInterface $cache = null;

    protected array $transformers = [];

    protected array $decorations = [];

    protected int $startingLineNumber = 1;

    protected Meta $meta;

    /**
     * @param  array<string, ParsedTheme>  $themes
     */
    public function __construct(
        protected string $code,
        protected ParsedGrammar $grammar,
        protected array $themes,
    ) {}

    /**
     * @param  Closure(string $code, ParsedGrammar $grammar): array<int, array<Token>>  $callback
     */
    public function generateTokensUsing(Closure $callback): self
    {
        $this->generateTokensUsing = $callback;

        return $this;
    }

    /**
     * @param  Closure(array<int, array<Token>> $tokens, array<string, ParsedTheme> $theme): array<int, array<HighlightedToken>>  $callback
     */
    public function highlightTokensUsing(Closure $callback): self
    {
        $this->highlightTokensUsing = $callback;

        return $this;
    }

    public function cache(?CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public function withGutter(bool $withGutter = true): self
    {
        $this->withGutter = $withGutter;

        return $this;
    }

    public function transformer(TransformerInterface $transformer): self
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    public function decoration(LineDecoration|PreDecoration|CodeDecoration|GutterDecoration ...$decorations): self
    {
        if (! Arr::any($this->transformers, fn (TransformerInterface $transformer) => $transformer instanceof DecorationTransformer)) {
            $this->transformers[] = new DecorationTransformer($this->decorations);
        }

        $this->decorations = array_merge($this->decorations, $decorations);

        return $this;
    }

    public function startingLine(int $lineNumber): self
    {
        $this->startingLineNumber = $lineNumber;

        return $this;
    }

    public function withMeta(Meta $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function cacheKey(): string
    {
        return 'phiki_html_'.md5(serialize([
            $this->code,
            $this->grammar->scopeName,
            array_keys($this->themes),
            ...array_map(fn (ParsedTheme $theme) => $theme->name, $this->themes),
            $this->withGutter,
            $this->startingLineNumber,
            ...array_map(fn (TransformerInterface $transformer) => get_class($transformer), $this->transformers),
        ]));
    }

    protected function callTransformerMethod(string $method, mixed ...$args): mixed
    {
        if ($this->transformers === []) {
            return $args;
        }

        foreach ($this->transformers as $transformer) {
            if (method_exists($transformer, $method)) {
                $args[0] = $transformer->{$method}(...$args);
            }
        }

        return $args;
    }

    private function getDefaultTheme(): ParsedTheme
    {
        return Arr::first($this->themes);
    }

    private function getDefaultThemeId(): string
    {
        return Arr::firstKey($this->themes);
    }

    public function __toString(): string
    {
        $cacheKey = $this->cacheKey();

        if (isset($this->cache) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        if (! isset($this->meta)) {
            $this->meta = new Meta;
        }

        foreach ($this->transformers as $transformer) {
            $transformer->withMeta($this->meta);

            if ($transformer instanceof RequiresGrammarInterface) {
                $transformer->withGrammar($this->grammar);
            }

            if ($transformer instanceof RequiresThemesInterface) {
                $transformer->withThemes($this->themes);
            }
        }

        [$code] = $this->callTransformerMethod('preprocess', $this->code);
        [$tokens] = $this->callTransformerMethod('tokens', call_user_func($this->generateTokensUsing, $code, $this->grammar));
        [$highlightedTokens] = $this->callTransformerMethod('highlighted', call_user_func($this->highlightTokensUsing, $tokens, $this->themes));

        $pre = new Element('pre');

        $pre->properties->set('class', $preClasses = (new ClassList)
            ->add(
                'phiki',
                $this->grammar->name ? "language-{$this->grammar->name}" : '',
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

        if ($this->grammar->name) {
            $pre->properties->set('data-language', $this->grammar->name);
        }

        $pre->properties->set('style', implode(';', $preStyles));

        $code = new Element('code', new Properties(['class' => new ClassList]));

        foreach ($highlightedTokens as $index => $lineTokens) {
            $line = new Element('span');
            $line->properties->set('class', new ClassList(['line']));

            if ($this->withGutter) {
                $gutter = new Element('span');
                $gutter->properties->set('class', new ClassList(['line-number']));

                $lineNumberColor = $this->getDefaultTheme()->colors['editorLineNumber.foreground'] ?? null;

                $gutter->properties->set('style', implode(';', array_filter([
                    $lineNumberColor ? "color: $lineNumberColor" : null,
                    '-webkit-user-select: none',
                ])));

                $gutter->children[] = new Text(sprintf('%2d', $this->startingLineNumber + $index));

                [$gutter] = $this->callTransformerMethod('gutter', $gutter, $index);

                $line->children[] = $gutter;
            }

            foreach ($lineTokens as $j => $token) {
                $span = new Element('span');

                $tokenStyles = [($token->settings[$this->getDefaultThemeId()] ?? null)?->toStyleString()];

                foreach ($token->settings as $id => $settings) {
                    if ($id !== $this->getDefaultThemeId()) {
                        $tokenStyles[] = $settings->toCssVarString($id);
                    }
                }

                $span->properties->set('class', new ClassList(['token']));
                $span->properties->set('style', implode(';', array_filter($tokenStyles)));
                $span->children[] = new Text(htmlspecialchars($token->token->text));

                [$span] = $this->callTransformerMethod('token', $span, $token, $j, $index);

                $line->children[] = $span;
            }

            [$line] = $this->callTransformerMethod('line', $line, $lineTokens, $index);

            $code->children[] = $line;
        }

        [$code] = $this->callTransformerMethod('code', $code);

        $pre->children[] = $code;

        [$pre] = $this->callTransformerMethod('pre', $pre);
        [$root] = $this->callTransformerMethod('root', new Root([$pre]));
        [$html] = $this->callTransformerMethod('postprocess', $root->__toString());

        if (isset($this->cache)) {
            $this->cache->set($cacheKey, $html);
        }

        return $html;
    }
}
