<?php

namespace Phiki\Extensions;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Environment\Environment;
use Phiki\Grammar\GrammarRepository;
use Phiki\Theme\ThemeRepository;

class DefaultExtension implements ExtensionInterface
{
    public function register(Environment $environment): void
    {
        $environment
            ->useGrammarRepository(new GrammarRepository)
            ->useThemeRepository(new ThemeRepository);
    }
}
