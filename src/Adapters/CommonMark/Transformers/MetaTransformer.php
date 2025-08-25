<?php

namespace Phiki\Adapters\CommonMark\Transformers;

use Phiki\Phast\Element;
use Phiki\Support\Arr;
use Phiki\Transformers\AbstractTransformer;

class MetaTransformer extends AbstractTransformer
{
    protected array $highlights = [];

    protected array $focuses = [];

    public function preprocess(string $code): string
    {
        $this->parse();

        return $code;
    }

    public function line(Element $span, array $tokens, int $index): Element
    {
        if (in_array($index + 1, $this->highlights, true)) {
            $span->properties->get('class')->add('highlight');
        }

        if (in_array($index + 1, $this->focuses, true)) {
            $span->properties->get('class')->add('focus');
        }

        return $span;
    }

    protected function parse(): void
    {
        if (! $this->meta->markdownInfo) {
            return;
        }

        [$highlights, $focuses] = array_pad(
            explode(
                '}{',
                rtrim(ltrim($this->meta->markdownInfo, '{'), '}'),
                2
            ),
            2,
            null
        );

        if (! $highlights && ! $focuses) {
            return;
        }

        $highlights = array_map(
            fn(array $part) => count($part) > 1 ? $part : $part[0],
            array_map(
                fn(string $part) => array_map(fn(string $number) => intval($number), explode('-', trim($part))),
                explode(',', $highlights)
            )
        );

        foreach ($highlights as $part) {
            if (is_array($part)) {
                $this->highlights = array_merge($this->highlights, range($part[0], $part[1]));
            } else {
                $this->highlights[] = $part;
            }
        }

        $this->highlights = array_unique($this->highlights);

        if (! $focuses) {
            return;
        }

        $focuses = array_map(
            fn(array $part) => count($part) > 1 ? $part : $part[0],
            array_map(
                fn(string $part) => array_map(fn(string $number) => intval($number), explode('-', trim($part))),
                explode(',', $focuses)
            )
        );

        foreach ($focuses as $part) {
            if (is_array($part)) {
                $this->focuses = array_merge($this->focuses, range($part[0], $part[1]));
            } else {
                $this->focuses[] = $part;
            }
        }

        $this->focuses = array_unique($this->focuses);
    }
}
