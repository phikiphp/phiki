<?php

namespace Phiki\Adapters\CommonMark\Transformers;

use Phiki\Phast\Element;
use Phiki\Transformers\AbstractTransformer;

class MetaTransformer extends AbstractTransformer
{
    /** @var array<int, array{int, int}> List of inclusive [start, end] line ranges. */
    protected array $highlights = [];

    /** @var array<int, array{int, int}> List of inclusive [start, end] line ranges. */
    protected array $focuses = [];

    public function preprocess(string $code): string
    {
        $this->parse();

        return $code;
    }

    public function pre(Element $pre): Element
    {
        if ($this->focuses !== []) {
            $pre->properties->get('class')->add('focus');
        }

        return $pre;
    }

    public function line(Element $span, array $tokens, int $index): Element
    {
        $lineNumber = $index + 1;

        if ($this->containsLine($this->highlights, $lineNumber)) {
            $span->properties->get('class')->add('highlight');
        }

        if ($this->containsLine($this->focuses, $lineNumber)) {
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

        $this->highlights = $this->parseLineRanges($highlights);
        $this->focuses = $this->parseLineRanges($focuses);
    }

    /**
     * Parse a comma-separated list of line numbers and ranges (e.g. "2,4-8") into
     * a list of inclusive [start, end] pairs.
     *
     * Ranges are deliberately never expanded into individual line numbers, since
     * the meta string is user-controlled and a range such as "1-100000000" would
     * otherwise exhaust memory. Reversed ranges (e.g. "5-2") are ignored, and
     * entries that don't match a real line (e.g. "0" or "abc") never match anything.
     *
     * @return array<int, array{int, int}>
     */
    protected function parseLineRanges(?string $spec): array
    {
        if (! $spec) {
            return [];
        }

        $ranges = [];

        foreach (explode(',', $spec) as $part) {
            $bounds = explode('-', trim($part));

            $start = intval($bounds[0]);
            $end = isset($bounds[1]) ? intval($bounds[1]) : $start;

            if ($end < $start) {
                continue;
            }

            $ranges[] = [$start, $end];
        }

        return $ranges;
    }

    /**
     * @param  array<int, array{int, int}>  $ranges
     */
    protected function containsLine(array $ranges, int $lineNumber): bool
    {
        foreach ($ranges as [$start, $end]) {
            if ($lineNumber >= $start && $lineNumber <= $end) {
                return true;
            }
        }

        return false;
    }
}
