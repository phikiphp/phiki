<?php

use Phiki\Environment\Environment;
use Phiki\Phiki;
use Phiki\Theme\Theme;

require_once __DIR__.'/../../vendor/autoload.php';

$grammar = $_GET['grammar'] ?? 'php';
$environment = Environment::default()->enableStrictMode();
$repository = $environment->getGrammarRepository();

$sample = file_get_contents(__DIR__.'/'.$grammar.'.sample');
$tokens = (new Phiki($environment))->codeToTokens($sample, $grammar);
$html = (new Phiki($environment))->codeToHtml($sample, $grammar, ['light' => Theme::OneLight, 'dark' => 'github-dark']);

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
        /* @media (prefers-color-scheme: dark) { */
            html.dark .phiki,
            html.dark .phiki span {
                color: var(--phiki-dark-color) !important;
                background-color: var(--phiki-dark-background-color) !important;
                font-style: var(--phiki-dark-font-style) !important;
                font-weight: var(--phiki-dark-font-weight) !important;
                text-decoration: var(--phiki-dark-text-decoration) !important;
            }
        /* } */

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
                <?php foreach ($repository->getAllGrammarNames() as $g) { ?>
                    <option value="<?= $g ?>" <?= $grammar === $g ? 'selected' : '' ?>>
                        <?= $g ?>
                    </option>
                <?php } ?>
            </select>
        </form>

        <?= $html ?>

        <?php dump($tokens); ?>
    </main>
</body>

</html>