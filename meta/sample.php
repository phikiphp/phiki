<?php

set_time_limit(2);

use Phiki\Environment\Environment;
use Phiki\Phiki;
use Phiki\Theme\Theme;

require_once __DIR__.'/../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$grammar = $_GET['grammar'] ?? 'php';
$withGutter = ($_GET['gutter'] ?? false) === 'on';
$environment = Environment::default()->enableStrictMode();
/** @var \Phiki\Grammar\GrammarRepository $repository */
$repository = $environment->getGrammarRepository();
$grammars = $repository->getAllGrammarNames();
natsort($grammars);

$sample = file_get_contents(__DIR__.'/../resources/samples/'.$grammar.'.sample');
$tokens = (new Phiki($environment))->codeToTokens($sample, $grammar);
$html = (new Phiki($environment))->codeToHtml($sample, $grammar, ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark], $withGutter);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiki Sample Explorer</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        @media (prefers-color-scheme: dark) {

            html.dark .phiki,
            html.dark .phiki span {
                color: var(--phiki-dark-color) !important;
                background-color: var(--phiki-dark-background-color) !important;
                font-style: var(--phiki-dark-font-style) !important;
                font-weight: var(--phiki-dark-font-weight) !important;
                text-decoration: var(--phiki-dark-text-decoration) !important;
            }
        }

        pre {
            padding: 0.875rem;
            padding-left: 0.5rem;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            border-radius: 10px;
        }

        code {
            font-family: 'Fira Code';
        }

        pre code span[data-line]::before {
            content: attr(data-line);
            display: inline-block;
            width: 1.7rem;
            margin-right: 1rem;
            color: #666;
            text-align: right;
        }

        pre code span.line-number {
            padding-right: 10px;
        }
    </style>
</head>

<body class="antialiased bg-neutral-950 text-white p-8 space-y-8">
    <header>
        <h1 class="font-bold text-xl">
            Phiki Sample Explorer
        </h1>
    </header>

    <main class="space-y-8">
        <form
            x-data
            class="flex items-center gap-x-4">
            <select name="grammar" x-on:change="$root.submit()" class="text-neutral-950">
                <?php foreach ($grammars as $g) { ?>
                    <option value="<?= $g ?>" <?= $grammar === $g ? 'selected' : '' ?>>
                        <?= $g ?>
                    </option>
                <?php } ?>
            </select>

            <div class="flex items-center gap-x-2.5">
                <input type="checkbox" name="gutter" id="gutter" x-on:change="$root.submit()" <?= $withGutter ? 'checked' : '' ?>>
                <label for="gutter">With gutter?</label>
            </div>
        </form>

        <?= $html ?>

        <?php dump($tokens); ?>
    </main>
</body>

</html>
