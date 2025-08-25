<?php

namespace Phiki\Adapters\Laravel\Components;

use Illuminate\View\Component;
use Phiki\Grammar\Grammar;
use Phiki\Theme\Theme;

class Code extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string|Grammar $grammar,
        public string|array|Theme $theme,
        public ?string $code = null,
        public bool $gutter = false,
        public int $startingLine = 1,
    ) {}

    /**
     * Render the component.
     */
    public function render(): string
    {
        return <<<'BLADE'
        {!! \Phiki\Adapters\Laravel\Facades\Phiki::codeToHtml($code ?? $slot->__toString(), $grammar, $theme)->withGutter($gutter)->startingLine($startingLine) !!}
        BLADE;
    }
}
