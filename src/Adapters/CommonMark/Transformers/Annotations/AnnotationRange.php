<?php

namespace Phiki\Adapters\CommonMark\Transformers\Annotations;

class AnnotationRange
{
    public function __construct(
        public AnnotationRangeKind $kind,
        public int $start,
        public int $end,
    ) {}

    public static function parse(string $range, int $index): ?self
    {
        $range = trim($range);

        // Highlight the current line plus the next N lines.
        if (preg_match('/^\d+$/', $range) === 1) {
            return new AnnotationRange(AnnotationRangeKind::Fixed, $index, (int) $range + $index);
        }

        // Highlight the current line plus the previous N lines.
        if (preg_match('/^-\d+$/', $range) === 1) {
            return new AnnotationRange(AnnotationRangeKind::Fixed, $index + (int) $range, $index);
        }

        // Start highlighting from OFFSET for a total of AND lines.
        if (preg_match('/^(?<offset>\d+),(?<and>\d+)$/', $range, $matches) === 1) {
            return new AnnotationRange(AnnotationRangeKind::Fixed, $index + (int) $matches['offset'], $index + (int) $matches['offset'] + (int) $matches['and'] - 1);
        }

        // Start highlighting from $index - OFFSET for a total of AND lines.
        if (preg_match('/^(?<offset>-\d+),(?<and>\d+)$/', $range, $matches) === 1) {
            return new AnnotationRange(AnnotationRangeKind::Fixed, $index + (int) $matches['offset'], $index + (int) $matches['offset'] + (int) $matches['and'] - 1);
        }

        // Start highlighting in an open-ended manner from the current line.
        if (preg_match('/^start$/i', $range) === 1) {
            return new AnnotationRange(AnnotationRangeKind::OpenEnded, $index, $index);
        }

        // Stop highlighting in an open-ended manner at the current line.
        if (preg_match('/^end$/i', $range) === 1) {
            return new AnnotationRange(AnnotationRangeKind::End, $index, $index);
        }

        return null;
    }
}
