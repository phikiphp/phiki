<?php

use Illuminate\Support\Facades\Blade;

it('can render the given code', function () {
    $output = Blade::render(<<<'BLADE'
<x-phiki::code grammar="php" theme="github-light">
echo "Hello, world!";
</x-phiki::code>
BLADE);

    expect($output)->toContain('<pre class="phiki language-php github-light"');
});

it('can render the given code with line numbers', function () {
    $output = Blade::render(<<<'BLADE'
<x-phiki::code grammar="php" theme="github-light" gutter>
echo "Hello, world!";
</x-phiki::code>
BLADE);

    expect($output)->toContain('1</span>');
});

it('can render the given code with a custom starting line number', function () {
    $output = Blade::render(<<<'BLADE'
<x-phiki::code grammar="php" theme="github-light" gutter :starting-line="10">
echo "Hello, world!";
</x-phiki::code>
BLADE);

    expect($output)->toContain('10</span>');
});

it('can accept code through an attribute', function () {
    $output = Blade::render(<<<'BLADE'
    <x-phiki::code grammar="php" theme="github-light" :$code />
    BLADE, [
        'code' => "<?php echo 'Hello, world!';",
    ]);

    expect($output)->toMatchSnapshot();
});
