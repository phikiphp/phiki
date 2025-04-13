<?php

set_time_limit(2);

use League\CommonMark\Environment\Environment as EnvironmentEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Phiki\CommonMark\PhikiExtension;
use Phiki\Environment\Environment;
use Phiki\Grammar\DefaultGrammars;
use Phiki\Phiki;
use Phiki\Theme\Theme;
use Phiki\Token\Token;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$grammar = $_GET['grammar'] ?? 'php';
$withGutter = ($_GET['gutter'] ?? false) === 'on';
$markdown = $_GET['markdown'] ?? null;
$environment = Environment::default()->enableStrictMode();
/** @var \Phiki\Grammar\GrammarRepository $repository */
$repository = $environment->getGrammarRepository();
$grammars = $repository->getAllGrammarNames();
natsort($grammars);

$sample = file_get_contents($samplePath = __DIR__ . '/../resources/samples/' . $grammar . '.sample');
$tokens = (new Phiki($environment))->codeToTokens($sample, $grammar);
$html = (new Phiki($environment))->codeToHtml($sample, $grammar, ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark], $withGutter);

$process = new Process(
    [
        'node',
        __DIR__ . '/../tests/Fixtures/vscode-textmate-compliance.js',
        $samplePath,
        array_flip(DefaultGrammars::SCOPES_TO_NAMES)[$grammar],
        json_encode(collect(DefaultGrammars::SCOPES_TO_NAMES)
            ->mapWithKeys(fn(string $name, string $scope) => [$scope => DefaultGrammars::NAMES_TO_PATHS[$name]])
            ->all()),
    ],
);

$process->run();

if (! $process->isSuccessful()) {
    throw new RuntimeException($process->getErrorOutput() . ':' . PHP_EOL . $process->getOutput());
}

$vscodeTextmateOutput = array_map(
    fn(array $lineTokens) => array_map(
        fn(array $token) => new Token(
            scopes: $token['scopes'],
            text: $token['text'],
            start: $token['start'],
            end: $token['end'],
        ),
        $lineTokens
    ),
    json_decode($process->getOutput(), true),
);

$tokenDiff = array_diff_multidimensional($tokens, $vscodeTextmateOutput, false);

$converter = new MarkdownConverter(
    (new EnvironmentEnvironment())
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new PhikiExtension('github-dark'))
);

$generatedMarkdown = $markdown ? $converter->convert($markdown)->getContent() : null;

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

        <div class="grid grid-cols-2 gap-10">
            <div>
                <p class="text-xl text-white mb-4">Phiki tokens:</p>
                <?php dump($tokens); ?>
            </div>

            <div>
                <p class="text-xl text-white mb-4">vscode-textmate tokens:</p>
                <?php dump($vscodeTextmateOutput); ?>
            </div>

            <div>
                <p class="text-xl text-white mb-4">Differences:</p>
                <?php dump($tokenDiff); ?>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-10 mt-10">
            <div>
                <p class="text-xl text-white mb-4">markdown:</p>
                <form>
                    <textarea name="markdown" class="w-full min-h-[300px] text-black"><?= $markdown ? htmlspecialchars($markdown) : null ?></textarea>
                    <button>generate</button>
                </form>
            </div>

            <div class="bg-white"><?= $generatedMarkdown ?></div>
        </div>
    </main>
</body>

</html>
