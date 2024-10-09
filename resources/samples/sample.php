<?php

use Phiki\Environment\Environment;
use Phiki\Grammar\GrammarRepository;
use Phiki\Phiki;

require_once __DIR__.'/../../vendor/autoload.php';

$grammar = $_GET['grammar'] ?? 'php';
$environment = Environment::default()->enableStrictMode();
$repository = $environment->getGrammarRepository();

$sample = file_get_contents(__DIR__.'/'.$grammar.'.sample');
$tokens = (new Phiki($environment))->codeToTokens($sample, $grammar);
$html = (new Phiki($environment))->codeToHtml($sample, $grammar, 'github-dark');

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
        pre {
            padding: 20px;
            font-size: 14px !important;
            line-height: 1.5 !important;
            border-radius: 10px;
        }

        code {
            font-family: 'Fira Code';
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