<?php

namespace Phiki\Tests\Fixtures;

use Phiki\Contracts\ExtensionInterface;
use Phiki\Environment\Environment;

class EmptyExtension implements ExtensionInterface
{
    public bool $registered = false;

    public function register(Environment $environment): void
    {
        $this->registered = true;
    }
}
