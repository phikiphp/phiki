<?php

namespace Phiki\Theme;

class ThemeColorExtractor
{
    public function __construct(
        protected ?ParsedTheme $theme = null
    ) {}

    /**
     * Get editor colors from the theme
     */
    public function getEditorColors(): array
    {
        if (! $this->theme) {
            return [];
        }

        return [
            'lineHighlight' => $this->theme->colors['editor.lineHighlightBackground'] ?? null,
            'findMatch' => $this->theme->colors['editor.findMatchBackground'] ?? null,
            'selection' => $this->theme->colors['editor.selectionBackground'] ?? null,
            'selectionHighlight' => $this->theme->colors['editor.selectionHighlightBackground'] ?? null,
        ];
    }

    /**
     * Get diff colors from markup token colors
     */
    public function getDiffColors(): array
    {
        if (! $this->theme) {
            return [];
        }

        $insertedColor = $this->theme->match(['markup.inserted']);
        $deletedColor = $this->theme->match(['markup.deleted']);

        return [
            'inserted' => $insertedColor ? [
                'background' => $insertedColor->background,
                'foreground' => $insertedColor->foreground,
            ] : null,
            'deleted' => $deletedColor ? [
                'background' => $deletedColor->background,
                'foreground' => $deletedColor->foreground,
            ] : null,
        ];
    }

    /**
     * Get a specific color by type
     */
    public function getColorForType(string $type): ?array
    {
        return match ($type) {
            'highlight' => [
                'background' => $this->getEditorColors()['lineHighlight'],
                'foreground' => null,
            ],
            'insert', 'add' => $this->getDiffColors()['inserted'],
            'remove', 'delete' => $this->getDiffColors()['deleted'],
            default => null,
        };
    }

    /**
     * Convert color array to CSS style string
     */
    public function colorsToStyle(array $colors): string
    {
        $styles = [];

        if (! empty($colors['background'])) {
            $styles[] = "background-color: {$colors['background']}";
        }

        if (! empty($colors['foreground'])) {
            $styles[] = "color: {$colors['foreground']}";
        }

        return implode('; ', $styles);
    }
}
