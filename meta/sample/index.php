<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Theme\Theme;

$grammar = Grammar::from($_GET['grammar'] ?? 'php');
$theme = Theme::from($_GET['theme'] ?? 'github-light');
$input = $_GET['input'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Phiki Sample Playground</title>
</head>

<body class="min-h-screen flex flex-col w-screen p-16 text-sm text-neutral-700 gap-y-16">
    <header>
        <h1 class="text-black text-lg font-medium tracking-tight">Phiki Sample Playground</h1>
        <p class="tracking-tight">Use this playground to experiment with various Phiki grammar files and themes.</p>
    </header>

    <body class="flex-1 flex flex-col">
        <form class="flex flex-col gap-y-12 flex-1">
            <div class="flex items-end gap-x-6">
                <div class="flex flex-col gap-y-2">
                    <label for="grammar" class="text-sm font-medium">Grammar</label>
                    <select name="grammar" id="grammar" class="border border-neutral-300 h-10 w-64 rounded px-2">
                        <?php foreach (Grammar::cases() as $g): ?>
                            <?php $selected = $g === $grammar ? 'selected' : ''; ?>
                            <option value="<?= $g->value ?>" <?= $selected ?>>
                                <?= $g->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-y-2">
                    <label for="theme" class="text-sm font-medium">Theme</label>
                    <select name="theme" id="theme" class="border border-neutral-300 h-10 w-64 rounded px-2">
                        <?php foreach (Theme::cases() as $t): ?>
                            <?php $selected = $t === $theme ? 'selected' : ''; ?>
                            <option value="<?= $t->value ?>" <?= $selected ?>>
                                <?= $t->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="h-10 px-4 bg-blue-500 border border-blue-600 hover:border-blue-700 text-white rounded hover:bg-blue-600 transition-colors">
                    Generate
                </button>
            </div>

            <div class="grid grid-cols-2 gap-x-12 flex-1">
                <textarea name="input" id="input" class="w-full h-full resize-none border border-neutral-200 rounded p-4 font-mono text-xs"><?= htmlspecialchars($input) ?></textarea>
                <div class="w-full h-full border border-neutral-200 rounded overflow-hidden flex flex-col [&_pre]:p-4 [&_pre]:flex-1 [&_pre]:overflow-auto [&_pre]:text-xs">
                    <?= (new Phiki)->codeToHtml($input, $grammar, $theme) ?>
                </div>
            </div>

            <div>
                <?php dump((new Phiki)->codeToTokens($input, $grammar)) ?>
            </div>
        </form>
    </body>
</body>

</html>
