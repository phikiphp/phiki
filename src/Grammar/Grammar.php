<?php

namespace Phiki\Grammar;

use Phiki\Contracts\GrammarRepositoryInterface;

enum Grammar: string
{
    case Txt = 'txt';
    case Php = 'php';
    case Javascript = 'javascript';
    case Json = 'json';
    case Jsonc = 'jsonc';
    case Jsonl = 'jsonl';
    case Yaml = 'yaml';
    case Css = 'css';
    case Scss = 'scss';
    case Postcss = 'postcss';
    case C = 'c';
    case Cpp = 'cpp';
    case Csv = 'csv';
    case Shellscript = 'shellscript';
    case Ini = 'ini';
    case Make = 'make';
    case Jsx = 'jsx';
    case Twig = 'twig';
    case Svelte = 'svelte';
    case Xml = 'xml';
    case Zig = 'zig';
    case Go = 'go';
    case Rust = 'rust';
    case Ruby = 'ruby';
    case Lua = 'lua';
    case Markdown = 'markdown';
    case Tsx = 'tsx';
    case Http = 'http';
    case Csharp = 'csharp';
    case Luau = 'luau';
    case Toml = 'toml';
    case Sql = 'sql';
    case Blade = 'blade';
    case Docker = 'docker';
    case Nginx = 'nginx';
    case Html = 'html';
    case Hack = 'hack';
    case CppMacro = 'cpp-macro';
    case HtmlDerivative = 'html-derivative';
    case Powershell = 'powershell';
    case Graphql = 'graphql';
    case Typescript = 'typescript';
    case Python = 'python';
    case Vue = 'vue';
    case Antlers = 'antlers';
    case Asm = 'asm';

    public function aliases(): array
    {
        return match ($this) {
            self::Shellscript => ['bash', 'sh', 'shell'],
            self::Javascript => ['js'],
            self::Yaml => ['yml'],
            self::Go => ['golang'],
            self::Txt => ['text', 'plaintext'],
            self::Markdown => ['md'],
            self::Python => ['py'],
            default => [],
        };
    }

    public function scopeName(): string
    {
        return match ($this) {
            self::Txt => 'text.txt',
            self::Php => 'source.php',
            self::Javascript => 'source.js',
            self::Json => 'source.json',
            self::Jsonc => 'source.json.comments',
            self::Jsonl => 'source.json.lines',
            self::Yaml => 'source.yaml',
            self::Css => 'source.css',
            self::Scss => 'source.css.scss',
            self::Postcss => 'source.css.postcss',
            self::C => 'source.c',
            self::Cpp => 'source.cpp',
            self::Csv => 'text.csv',
            self::Shellscript => 'source.shell',
            self::Ini => 'source.ini',
            self::Make => 'source.makefile',
            self::Jsx => 'source.js.jsx',
            self::Twig => 'text.html.twig',
            self::Svelte => 'source.svelte',
            self::Xml => 'text.xml',
            self::Zig => 'source.zig',
            self::Go => 'source.go',
            self::Rust => 'source.rust',
            self::Ruby => 'source.ruby',
            self::Lua => 'source.lua',
            self::Markdown => 'text.html.markdown',
            self::Tsx => 'source.tsx',
            self::Http => 'source.http',
            self::Csharp => 'source.cs',
            self::Luau => 'source.luau',
            self::Toml => 'source.toml',
            self::Sql => 'source.sql',
            self::Blade => 'text.html.php.blade',
            self::Docker => 'source.dockerfile',
            self::Nginx => 'source.nginx',
            self::Html => 'text.html.basic',
            self::Hack => 'source.hack',
            self::CppMacro => 'source.cpp.embedded.macro',
            self::HtmlDerivative => 'text.html.derivative',
            self::Powershell => 'source.powershell',
            self::Graphql => 'source.graphql',
            self::Typescript => 'source.ts',
            self::Python => 'source.python',
            self::Vue => 'source.vue',
            self::Antlers => 'text.html.statamic',
            self::Asm => 'source.asm.x86_64',
        };
    }

    public function path(): string
    {
        return match ($this) {
            default => __DIR__ . "/../../resources/languages/{$this->value}.json",
        };
    }

    public static function parse(array $grammar): ParsedGrammar
    {
        return (new GrammarParser)->parse($grammar);
    }

    public function toParsedGrammar(GrammarRepositoryInterface $repository): ParsedGrammar
    {
        return $repository->get($this->value);
    }
}
