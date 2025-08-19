<?php

use Phiki\Phast\Element;
use Phiki\Phast\Root;

it('can convert to string', function () {
    $root = new Root([
        new Element('div'),
        new Element('span'),
    ]);

    expect((string) $root)->toBe('<div></div><span></span>');
});
