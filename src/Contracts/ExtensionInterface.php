<?php

namespace Phiki\Contracts;

use Phiki\Environment\Environment;

interface ExtensionInterface
{
    public function register(Environment $environment): void;
}
