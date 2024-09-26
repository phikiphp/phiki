<?php

use Phiki\Phiki;

describe('Phiki', function () {
    it('can be constructed', function () {
        expect(new Phiki())->toBeInstanceOf(Phiki::class);
    });
});