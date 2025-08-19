![Phiki](./art/banner.png)

Phiki is a syntax highlighter written in PHP. It uses TextMate grammar files and Visual Studio Code themes to generate syntax highlighted code for the web.

The name and public API of Phiki is heavily inspired by [Shiki](https://shiki.style/), a package that does more or less the same thing in the JavaScript ecosystem. The actual implementation of the package is also heavily inspired by [`vscode-textmate`](https://github.com/microsoft/vscode-textmate) which is the powerhouse of a package behind Visual Studio Code, Shiki, and others.

## Installation

Install Phiki via Composer:

```sh
composer require phiki/phiki
```

## Getting Started

The fastest way to get started is with the `codeToHtml()` method.

```php
use Phiki\Phiki;
use Phiki\Grammar\Grammar;
use Phiki\Theme\Theme;

$phiki = new Phiki();

$html = $phiki->codeToHtml(
    <<<'PHP'
    echo "Hello, world!";
    PHP,
    Grammar::Php,
    Theme::GithubDark,
);
```

This method takes in the code you want to highlight, the target language, as well as the theme you want to use. It then returns the generated HTML as a string. 

> [!NOTE]
> All of Phiki's styling is applied using inline `style` attributes, so there's no need to add any CSS to your project.

### Supported Languages

Phiki ships with 200+ grammars and 50+ themes. To provide a clean developer experience, you can find all supported grammars and themes when using the `Phiki\Grammar\Grammar` and `Phiki\Theme\Theme` enums.

These files are auto-generated when pulling in grammar and theme files from remote repositories so are always up-to-date.

### CommonMark Integration

Phiki provides a convenient extension for the excellent `league/commonmark` package so that you can start using it on your blog or documentation site with very little effort.

All you need to do is register the extension through a CommonMark `Environment` object.

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\CommonMark\PhikiExtension;

$environment = new Environment;
$environment
    ->addExtension(new CommonMarkCoreExtension)
    ->addExtension(new PhikiExtension('github-dark'));

$converter = new MarkdownConverter($environment);
$output = $converter->convert(<<<'MD'
    ```html
    <p>Hello, world!</p>
    ```
    MD);
```

### Laravel

If you're using Laravel's `Str::markdown()` or `str()->markdown()` methods, you can use the same CommonMark extension by passing it through to the method.

```php
use Phiki\CommonMark\PhikiExtension;

Str::markdown('...', extensions: [
    new PhikiExtension('github-dark'),
]); 
```

### Using custom languages and themes

To use a language or theme that Phiki doesn't support, you need to register it with a `GrammarRepository` or `ThemeRepository`.

This can be done by building a custom `Environment` object and telling Phiki to use this instead of the default one.

```php
use Phiki\Environment\Environment;

$environment = Environment::default();

// Register a custom language.
$environment
    ->getGrammarRepository()
    ->register('my-language', __DIR__ . '/../path/to/grammar.json');

$environment
    ->getThemeRepository()
    ->register('my-theme', __DIR__ . '/../path/to/theme.json');

$phiki = new Phiki($environment);

$phiki->codeToHtml('...', 'my-language', 'my-theme');
```

### Line numbers

Each line has its own `<span>` element with a `data-line` attribute, so you can use CSS to display line numbers in the generated HTML. The benefit to this approach is that the text isn't selectable so you code snippets can be highlighted the same as before.

```css
pre code span[data-line]::before {
    content: attr(data-line);
    display: inline-block;
    width: 1.7rem;
    margin-right: 1rem;
    color: #666;
    text-align: right;
}
```

These styles are of course just a guide. You can change the colors and sizing to your own taste.

### Multi-theme support

Phiki has support for highlighting code with multiple themes. This is great for sites that have a color scheme switcher, allowing you to change the theme used in each mode.

To take advantage of this, pass an array of themes to the `codeToHtml()` method.

```php
$phiki->codeToHtml("...", Grammar::Php, [
    'light' => Theme::GithubLight,
    'dark' => Theme::GithubDark,
]);
```

The first entry in the array will be used as the default theme. Other themes in the array will add additional CSS variables to the `style` attribute on each token, as well as the surrounding `<pre>` element. This means you'll need to use some CSS on your site to switch between the different themes.

**Query-based dark mode**

```css
@media (prefers-color-scheme: dark) {
    .phiki,
    .phiki span {
        color: var(--phiki-dark-color) !important;
        background-color: var(--phiki-dark-background-color) !important;
        font-style: var(--phiki-dark-font-style) !important;
        font-weight: var(--phiki-dark-font-weight) !important;
        text-decoration: var(--phiki-dark-text-decoration) !important;
    }
}
```

**Class-based dark mode**

```css
html.dark .phiki,
html.dark .phiki span {
    color: var(--phiki-dark-color) !important;
    background-color: var(--phiki-dark-background-color) !important;
    font-style: var(--phiki-dark-font-style) !important;
    font-weight: var(--phiki-dark-font-weight) !important;
    text-decoration: var(--phiki-dark-text-decoration) !important;
}
```

Phiki doesn't limit you to light and dark mode themes – you can use any key you wish in the array and CSS variables will be generated accordingly. You can then adjust the CSS on your site to apply those styles accordingly.

#### Usage with CommonMark extension

Multiple themes can also be used with the [CommonMark extension](#commonmark-integration) by passing an array to the extension object.

```php
$environment
    ->addExtension(new CommonMarkCoreExtension)
    ->addExtension(new PhikiExtension([
        'light' => Theme::GithubLight,
        'dark' => Theme::GithubDark,
    ]));
```

## Credits

* [Ryan Chandler](https://github.com/ryangjchandler)
* [Shiki](https://shiki.style/) for API inspiration and TextMate grammar files via [`tm-grammars` and `tm-themes`](https://github.com/shikijs/textmate-grammars-themes).
* [`vscode-textmate`](https://github.com/microsoft/vscode-textmate) for guiding the implementation of the internal tokenizer.
