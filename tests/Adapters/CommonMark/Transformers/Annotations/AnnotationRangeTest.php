<?php

use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationRange;
use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationRangeKind;

it('can parse a simple range', function () {
    $range = AnnotationRange::parse('1', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::Fixed)
        ->start->toBe(5)
        ->end->toBe(6);
});

it('can parse a negative range', function () {
    $range = AnnotationRange::parse('-1', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::Fixed)
        ->start->toBe(4)
        ->end->toBe(5);
});

it('can parse a comma-separated range', function () {
    $range = AnnotationRange::parse('1,3', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::Fixed)
        ->start->toBe(6)
        ->end->toBe(8);

    $range = AnnotationRange::parse('5,3', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::Fixed)
        ->start->toBe(10)
        ->end->toBe(12);
});

it('can parse a comma-separated range with negative offset', function () {
    $range = AnnotationRange::parse('-1,10', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::Fixed)
        ->start->toBe(4)
        ->end->toBe(13);
});

it('can parse an open ended range', function () {
    $range = AnnotationRange::parse('start', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::OpenEnded)
        ->start->toBe(5)
        ->end->toBe(5);
});

it('can parse an end range', function () {
    $range = AnnotationRange::parse('end', 5);

    expect($range)
        ->kind->toBe(AnnotationRangeKind::End)
        ->start->toBe(5)
        ->end->toBe(5);
});

it('returns null for invalid ranges', function () {
    expect(AnnotationRange::parse('foo', 5))->toBeNull();
    expect(AnnotationRange::parse('1,foo', 5))->toBeNull();
    expect(AnnotationRange::parse('foo,1', 5))->toBeNull();
    expect(AnnotationRange::parse('1,1,1', 5))->toBeNull();
});
