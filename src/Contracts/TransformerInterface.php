<?php

namespace Phiki\Contracts;

use Phiki\Html\Code;
use Phiki\Html\Element;
use Phiki\Html\Pre;
use Phiki\Html\Root;
use Phiki\Html\Span;

interface TransformerInterface
{
    /**
     * Pre-process the code before it gets sent through the tokenizer and highlighter.
     */
    public function preprocess(string $code): string;

    /**
     * Modify the list of highlighted line tokens before they get sent to the HTML generator.
     *
     * @param  array<int, array<int, \Phiki\Token\HighlightedToken>>  $tokens
     * @return array<int, array<int, \Phiki\Token\HighlightedToken>>
     */
    public function tokens(array $tokens): array;

    /**
     * Transform the entire HTML tree before it gets converted to a string.
     */
    public function root(Root $root): Root;

    /**
     * Modify the `pre` element.
     */
    public function pre(Pre $pre): Pre;

    /**
     * Modify the `code` element.
     */
    public function code(Code $code): Code;

    /**
     * Modify a line's element.
     *
     * By default, this method will receive a `Span` but if another transformer returns an `Element` then this method will receive that `Element` instead.
     *
     * @return \Phiki\Html\Span|\Phiki\Html\Element|null Return `null` to remove the line, or return an `Element` to replace it.
     */
    public function line(Element $element, int $line): Span|Element|null;

    /**
     * Modify a token's element.
     *
     * * By default, this method will receive a `Span` but if another transformer returns an `Element` then this method will receive that `Element` instead.
     *
     * @return \Phiki\Html\Span|\Phiki\Html\Element|null Return `null` to remove the token, or return an `Element` to replace it.
     */
    public function token(Element $element): Span|Element|null;

    /**
     * Post-process the HTML after it has been generated.
     */
    public function postprocess(string $html): string;
}
