<?php

namespace Phiki\Adapters\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;
use Phiki\Phiki;
use Phiki\Theme\Theme;

class PhikiExtension implements ConfigurableExtensionInterface
{
    /**
     * @param  bool  $withGutter  Include a gutter in the generated HTML. The gutter typically contains line numbers and helps provide context for the code.
     */
    public function __construct(
        private string|array|Theme $theme = Theme::Nord,
        private Phiki $phiki = new Phiki,
        private bool $withGutter = false,
    ) {}

    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('phiki', Expect::structure([
            'theme' => Expect::mixed()->default($this->theme),
            'with_gutter' => Expect::bool()->default($this->withGutter),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $config = $environment->getConfiguration();

        $theme = $config->get('phiki/theme');
        $withGutter = $config->get('phiki/with_gutter');

        $environment->addRenderer(
            FencedCode::class,
            new CodeBlockRenderer($theme, $this->phiki, $withGutter),
            10,
        );
    }
}
