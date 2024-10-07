<?php

use Phiki\Support\Str;

require_once __DIR__.'/../vendor/autoload.php';

function main()
{
    $nodeModules = realpath(__DIR__.'/../node_modules');
    $grammarsDirectory = realpath($nodeModules.'/tm-grammars/grammars');
    $themesDirectory = realpath($nodeModules.'/tm-themes/themes');

    $grammars = [];
    $themes = [];

    echo "Copying grammar files...\n";

    eachFile($grammarsDirectory, function (SplFileInfo $grammar) use (&$grammars) {
        $json = json_decode(file_get_contents($grammar->getRealPath()), true);

        // NOTE: We don't support these grammar types yet.
        if (isset($json['injectionSelector'])) {
            return;
        }

        copy($grammar->getRealPath(), __DIR__.'/../resources/languages/'.$grammar->getFilename());

        $grammars[] = [
            'path' => $grammar->getFilename(),
            'name' => $json['name'] ?? basename($grammar->getFilename(), $grammar->getExtension()),
            'scopeName' => $json['scopeName'],
        ];
    });

    echo "Copying theme files...\n";

    eachFile($themesDirectory, function (SplFileInfo $theme) use (&$themes) {
        copy($theme->getRealPath(), __DIR__.'/../resources/themes/'.$theme->getFilename());

        $json = json_decode(file_get_contents($theme->getRealPath()), true);

        $themes[] = [
            'path' => $theme->getFilename(),
            'name' => $json['name'] ?? basename($theme->getFilename(), $theme->getExtension()),
        ];
    });

    echo "Generating DefaultGrammars class...\n";

    $defaultGrammarsStub = file_get_contents(__DIR__.'/stubs/DefaultGrammars.php.stub');
    $namesToPaths = [];
    $scopesToNames = [];

    foreach ($grammars as $grammar) {
        $namesToPaths[] = sprintf('"%s" => __DIR__ . "/../../resources/languages/%s"', $grammar['name'], $grammar['path']);
        $scopesToNames[$grammar['scopeName']] = sprintf('"%s" => "%s"', $grammar['scopeName'], $grammar['name']);
    }

    $namesToPathsString = implode(",\n", $namesToPaths);
    $scopesToNamesString = implode(",\n", $scopesToNames);

    $defaultGrammarsStub = sprintf($defaultGrammarsStub, $namesToPathsString, $scopesToNamesString);

    file_put_contents(__DIR__.'/../src/Generated/DefaultGrammars.php', $defaultGrammarsStub);

    echo "Generating Grammar enum...\n";

    $grammarEnumStub = file_get_contents(__DIR__.'/stubs/Grammar.php.stub');
    $grammarCases = [];

    foreach ($grammars as $grammar) {
        $grammarCases[] = sprintf('case %s = "%s";', Str::studly($grammar['name']), $grammar['name']);
    }

    $grammarCases = implode("\n", $grammarCases);
    $grammarEnumStub = sprintf($grammarEnumStub, $grammarCases);

    file_put_contents(__DIR__.'/../src/Grammar/Grammar.php', $grammarEnumStub);

    echo "Generating DefaultThemes class...\n";

    $defaultThemesStub = file_get_contents(__DIR__.'/stubs/DefaultThemes.php.stub');
    $namesToPaths = [];

    foreach ($themes as $theme) {
        $namesToPaths[] = sprintf('"%s" => __DIR__ . "/../../resources/themes/%s"', $theme['name'], $theme['path']);
    }

    $namesToPathsString = implode(",\n", $namesToPaths);

    $defaultThemesStub = sprintf($defaultThemesStub, $namesToPathsString);

    file_put_contents(__DIR__.'/../src/Generated/DefaultThemes.php', $defaultThemesStub);

    echo "Generating Theme enum...\n";

    $themeEnumStub = file_get_contents(__DIR__.'/stubs/Theme.php.stub');
    $themeCases = [];

    foreach ($themes as $theme) {
        $themeCases[] = sprintf('case %s = "%s";', Str::studly($theme['name']), $theme['name']);
    }

    $themeCases = implode("\n", $themeCases);
    $themeEnumStub = sprintf($themeEnumStub, $themeCases);

    file_put_contents(__DIR__.'/../src/Theme/Theme.php', $themeEnumStub);

    echo "Done!\n";
}

function eachFile(string $path, Closure $callback): void
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $callback($file);
        }
    }
}

main();
