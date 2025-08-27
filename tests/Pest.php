<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\Adapters\CommonMark\PhikiExtension;
use Phiki\Grammar\Grammar;
use Phiki\Grammar\GrammarRepository;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;

pest()->uses(\Phiki\Tests\Adapters\Laravel\TestCase::class)->in('Adapters/Laravel');

function tokenize(string $input, array|Grammar $grammar): array
{
    if (! $grammar instanceof Grammar) {
        if (! isset($grammar['scopeName'])) {
            $grammar['scopeName'] = 'source.test';
        }

        $parsedGrammar = ParsedGrammar::fromArray($grammar);
    } else {
        $parsedGrammar = $grammar->toParsedGrammar(new GrammarRepository);
    }

    return (new Phiki)->codeToTokens($input, $parsedGrammar);
}

function highlight(array $tokens, array $theme): array
{
    if (! isset($theme['default'])) {
        $theme = ['default' => $theme];
    }

    foreach ($theme as &$value) {
        if (! isset($value['name'])) {
            $value['name'] = 'test';
        }

        if (! isset($value['colors'])) {
            $value['colors'] = [
                'editor.background' => '#000',
                'editor.foreground' => '#fff',
            ];
        }

        $value = Theme::parse($value);
    }

    return (new Phiki)->tokensToHighlightedTokens($tokens, $theme);
}

function markdown(string $input, Theme $theme = Theme::GithubLight, ?Grammar $grammar = null): string
{
    $environment = new Environment;
    $environment->addExtension(new CommonMarkCoreExtension)->addExtension(new PhikiExtension($theme));
    $converter = new MarkdownConverter($environment);

    $markdown = $grammar === null ? $input : <<<MD
```{$grammar->value}
{$input}
```
MD;

    return $converter->convert($markdown)->getContent();
}
