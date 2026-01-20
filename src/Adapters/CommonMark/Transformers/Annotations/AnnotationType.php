<?php

namespace Phiki\Adapters\CommonMark\Transformers\Annotations;

enum AnnotationType
{
    case Highlight;
    case Focus;
    case Insert;
    case Remove;

    /**
     * Get the keywords used to denote this annotation type.
     *
     * e.g. highlight can be denoted by `[code! highlight]`.
     */
    public function keywords(): array
    {
        return match ($this) {
            self::Highlight => ['highlight', 'hl', '~~'],
            self::Focus => ['focus', 'f', '**'],
            self::Insert => ['insert', 'add', '++'],
            self::Remove => ['remove', 'del', 'delete', '--'],
        };
    }

    /**
     * Get the CSS classes to apply to lines with this annotation.
     */
    public function getLineClasses(): array
    {
        return match ($this) {
            self::Highlight => ['highlight'],
            self::Focus => ['focus'],
            self::Insert => ['insert'],
            self::Remove => ['remove'],
        };
    }

    /**
     * Get the CSS classes to apply to the pre element.
     */
    public function getPreClasses(): array
    {
        return match ($this) {
            self::Focus => ['focus'],
            default => [],
        };
    }
    
    /**
     * Get the gutter symbol to insert.
     */
    public function getGutterSymbol(): ?string
    {
        return match ($this) {
            self::Insert => ' +',
            self::Remove => ' -',
            default => null,
        };
    }

    /**
     * Get the type from the given keyword.
     */
    public static function fromKeyword(string $keyword): self
    {
        return match ($keyword) {
            'highlight', 'hl', '~~' => self::Highlight,
            'focus', 'f', '**' => self::Focus,
            'insert', 'add', '++' => self::Insert,
            'remove', 'del', 'delete', '--' => self::Remove,
            default => throw new \InvalidArgumentException("Unknown annotation keyword: {$keyword}"),
        };
    }
}
