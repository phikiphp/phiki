![Phiki](./art/banner.png)

Phiki is a syntax highlighter written in PHP. It uses TextMate grammar files and Visual Studio Code themes to generate syntax highlighted code for the web and terminal.

## Installation

Install Phiki via Composer:

```sh
composer require ryangjchandler/phiki
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

### Terminal Output

Phiki has support for generating output designed for use in the terminal. This is available through the `codeToTerminal()` method which accepts the same parameters as the `codeToHtml()` method.

```php
echo $phiki->codeToTerminal('echo "Hello, world"!', Grammar::Php, Theme::GithubDark);
```

![](./art/codeToTerminal.png)

## Known Limitations & Implementation Notes

The implementation of this package is inspired by existing art, namely `vscode-textmate`. The main reason that implementing a TextMate-based syntax highlighter in PHP is a complex task is down to the fact that `vscode-textmate` (and the TextMate editor) uses the [Oniguruma](https://github.com/kkos/oniguruma) engine for handling regular expressions.

PHP uses the PCRE2 engine which doesn't have support for all of Oniguruma's features. To reduce the risk of broken RegExs, Phiki performs a series of transformations with solid success rates:

* Properly escape unescaped forward-slashes (`/`).
* Translate `\h` and `\H` to PCRE equivalents.
* Translate `\p{xx}` to PCRE-compatible versions.
* Escape invalid leading range characters (`[-...]`).
* Properly escape unescaped close-set characters (`]`).
* Translate unsupported Unicode escape sequences (`\uXXXX`).

One of the biggest differences between PCRE2 and Oniguruma is that Oniguruma has support for "variable-length lookbehinds". Variable-length lookbehinds, both positive and negative, are normally created when a quantifier such as `+` or `*` is used inside of the lookbehind.

PCRE2 **does not** support these types of lookbehinds and they're essentially impossible to translate into PCRE2-compatible equivalents. In these cases, Phiki also performs a series of manual "patches" on grammar files to get RegExs as close as possible to the intended output.

**These patches are not perfect** – there is still a chance of running into errors in your application when highlighting code! If you do encounter an error with a message like the one below, please check the [Issues](https://github.com/ryangjchandler/phiki/issues) page or create a new issue with information about the grammar / language you're highlighting and a reproduction case.

```
preg_match(): Compilation failed: length of lookbehind assertion is not limited at offset...
```

## Credits

* [Ryan Chandler](https://github.com/ryangjchandler)