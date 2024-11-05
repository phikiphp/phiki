<?php

namespace Phiki\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExtensionInterface;
use Phiki\Phiki;
use Phiki\Theme\Theme;

class PhikiExtension implements ExtensionInterface
{
    /**
     * @param  bool  $withWrapper  Wrap the generated HTML in an additional `<div>` so that it can be styled with CSS. Useful for avoiding overflow issues.
     */
    public function __construct(
        private string|Theme $theme,
        private Phiki $phiki = new Phiki,
        private bool $withWrapper = false,
    ) {}

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addRenderer(FencedCode::class, new CodeBlockRenderer($this->theme, $this->phiki, $this->withWrapper), 10);
    }
}
