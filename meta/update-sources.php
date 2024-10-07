<?php

require_once __DIR__ . '/../vendor/autoload.php';

function main() {
    $nodeModules = realpath(__DIR__ . '/../node_modules');
    $grammarsDirectory = realpath($nodeModules . '/tm-grammars/grammars');
    $themesDirectory = realpath($nodeModules . '/tm-themes/themes');

    $grammars = [];
    $themes = [];

    echo "Copying grammar files...\n";

    eachFile($grammarsDirectory, function (SplFileInfo $grammar) use (&$grammars) {
        copy($grammar->getRealPath(), __DIR__ . '/../languages/' . $grammar->getFilename());

        $json = json_decode(file_get_contents($grammar->getRealPath()), true);

        $grammars[] = [
            'path' => $grammar->getFilename(),
            'name' => $json['name'] ?? basename($grammar->getFilename(), $grammar->getExtension()),
            'scopeName' => $json['scopeName'],
        ];
    });

    echo "Copying theme files...\n";

    eachFile($themesDirectory, function (SplFileInfo $theme) use (&$themes) {
        copy($theme->getRealPath(), __DIR__ . '/../themes/' . $theme->getFilename());

        $json = json_decode(file_get_contents($theme->getRealPath()), true);

        $themes[] = [
            'path' => $theme->getFilename(),
            'name' => $json['name'] ?? basename($theme->getFilename(), $theme->getExtension()),
        ];
    });

    echo "Generating DefaultGrammars class...\n";

    $defaultGrammarsStub = file_get_contents(__DIR__ . '/stubs/DefaultGrammars.php.stub');
    $namesToPaths = [];
    $scopesToNames = [];
    
    foreach ($grammars as $grammar) {
        $namesToPaths[] = sprintf('"%s" => __DIR__ . "/../../languages/%s"', $grammar['name'], $grammar['path']);
        $scopesToNames[$grammar['scopeName']] = sprintf('"%s" => "%s"', $grammar['scopeName'], $grammar['name']);
    }

    $namesToPathsString = implode(",\n", $namesToPaths);
    $scopesToNamesString = implode(",\n", $scopesToNames);
    
    $defaultGrammarsStub = sprintf($defaultGrammarsStub, $namesToPathsString, $scopesToNamesString);

    file_put_contents(__DIR__ . '/../src/Generated/DefaultGrammars.php', $defaultGrammarsStub);

    echo "Generating DefaultThemes class...\n";

    $defaultThemesStub = file_get_contents(__DIR__ . '/stubs/DefaultThemes.php.stub');
    $namesToPaths = [];

    foreach ($themes as $theme) {
        $namesToPaths[] = sprintf('"%s" => __DIR__ . "/../../themes/%s"', $theme['name'], $theme['path']);
    }

    $namesToPathsString = implode(",\n", $namesToPaths);

    $defaultThemesStub = sprintf($defaultThemesStub, $namesToPathsString);

    file_put_contents(__DIR__ . '/../src/Generated/DefaultThemes.php', $defaultThemesStub);

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