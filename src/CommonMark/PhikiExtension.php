<?php

namespace Phiki\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExtensionInterface;
use Phiki\Phiki;

class PhikiExtension implements ExtensionInterface
{
    public function __construct(
        private string $theme,
        private Phiki $phiki = new Phiki,
    ) {}

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addRenderer(FencedCode::class, new CodeBlockRenderer($this->theme, $this->phiki), 10);
    }
}
