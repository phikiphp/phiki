<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Theme\Theme;

require_once __DIR__ . '/../vendor/autoload.php';

$environment = new Environment();
$environment->addExtension(new CommonMarkCoreExtension)->addExtension(new PhikiExtension(Theme::GithubLight));
$converter = new MarkdownConverter($environment);

$markdown = <<<'MARKDOWN'
```blade
{{-- [code! highlight:start] --}}
@if(true) {{-- [code! focus:start] --}}
    Hello, world!
@endif {{-- [code! highlight:end] --}}

{{ $variable }} {{-- [code! focus:end] --}}
```
MARKDOWN;

echo $converter->convert($markdown);
