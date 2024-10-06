<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\CommonMark\PhikiExtension;

describe('CommonMark > Extension', function () {
    it('registers renderers', function () {
        $environment = new Environment;

        $environment
            ->addExtension(new CommonMarkCoreExtension)
            ->addExtension(new PhikiExtension('github-dark'));

        $markdown = new MarkdownConverter($environment);
        $generated = $markdown->convert(<<<'MD'
        ```php
        class A {}
        ```
        MD)->getContent();

        expect($generated)
            ->toContain('phiki')
            ->toContain('github-dark')
            ->toContain('<span class="token" style="color: #b392f0;">A</span>');
    });
});
