// Time vscode-textmate's tokenization and Shiki's highlighting of each grammar's sample, for
// meta/performance.php. Both use Phiki's bundled Oniguruma grammars and theme, so they work from
// the same input as Phiki.
//
// Usage: node meta/performance.js <output.json> <theme> <runs> <grammar> [grammar ...]
// Writes {grammar: {tokenize: ms, highlight: ms}}, with the median of the given number of runs.

import fs from 'node:fs'
import path from 'node:path'
import { performance } from 'node:perf_hooks'
import vsctm from 'vscode-textmate'
import oniguruma from 'vscode-oniguruma'
import { createHighlighterCore } from '@shikijs/core'
import { createOnigurumaEngine } from '@shikijs/engine-oniguruma'

const [output, themeName, runs, ...requested] = process.argv.slice(2)

if (! output || ! themeName || ! runs || requested.length === 0) {
    console.error('Usage: node meta/performance.js <output.json> <theme> <runs> <grammar> [grammar ...]')
    process.exit(1)
}

const root = path.join(import.meta.dirname, '..')
const wasm = fs.readFileSync(path.join(root, 'node_modules/vscode-oniguruma/release/onig.wasm'))
const grammarsDir = path.join(root, 'resources/grammars/oniguruma')
const grammars = {}
const scopeNames = {}

for (const file of fs.readdirSync(grammarsDir)) {
    const grammar = JSON.parse(fs.readFileSync(path.join(grammarsDir, file), 'utf8'))

    grammars[grammar.scopeName] = grammar
    scopeNames[path.basename(file, '.json')] = grammar.scopeName
}

const theme = JSON.parse(fs.readFileSync(path.join(root, `resources/themes/${themeName}.json`), 'utf8'))
const sample = (name) => fs.readFileSync(path.join(root, `tests/Fixtures/samples/${name}.sample`), 'utf8')

// Run as many times untimed first, to load and compile the grammars and let V8 optimize the code,
// then return the median of the timed runs.
function time(fn) {
    for (let i = 0; i < Number(runs); i++) {
        fn()
    }

    const times = []

    for (let i = 0; i < Number(runs); i++) {
        const start = performance.now()
        fn()
        times.push(performance.now() - start)
    }

    times.sort((a, b) => a - b)

    return times[Math.floor(times.length / 2)]
}

await oniguruma.loadWASM(wasm.buffer.slice(wasm.byteOffset, wasm.byteOffset + wasm.byteLength))

// The scope names of the grammar and every grammar it includes or is injected with, so each
// sample loads only what it uses.
function dependencies(scopeName) {
    const found = new Set()
    const queue = [scopeName]

    while (queue.length > 0) {
        const current = queue.pop()

        if (found.has(current) || ! grammars[current]) {
            continue
        }

        found.add(current)

        for (const [, include] of JSON.stringify(grammars[current]).matchAll(/"include":"([^"#$][^"#]*)/g)) {
            queue.push(include)
        }

        for (const grammar of Object.values(grammars)) {
            if (grammar.injectTo?.includes(current)) {
                queue.push(grammar.scopeName)
            }
        }
    }

    return [...found]
}

const engine = createOnigurumaEngine(import('@shikijs/engine-oniguruma/wasm-inlined'))
const langNames = Object.fromEntries(Object.entries(scopeNames).map(([name, scopeName]) => [scopeName, name]))
const results = {}

for (const name of requested) {
    const code = sample(name)
    const lines = code.split('\n')
    const scopes = dependencies(scopeNames[name])

    // A registry and highlighter per grammar, disposed afterwards, so compiled scanners don't
    // exhaust the WASM heap.
    const registry = new vsctm.Registry({
        onigLib: Promise.resolve({
            createOnigScanner: (patterns) => new oniguruma.OnigScanner(patterns),
            createOnigString: (string) => new oniguruma.OnigString(string),
        }),
        loadGrammar: async (scopeName) => grammars[scopeName] ? vsctm.parseRawGrammar(JSON.stringify(grammars[scopeName]), `${scopeName}.json`) : null,
    })
    const highlighter = await createHighlighterCore({
        themes: [theme],
        langs: scopes.map((scopeName) => ({ ...grammars[scopeName], name: langNames[scopeName] ?? scopeName })),
        engine,
    })
    const grammar = await registry.loadGrammar(scopeNames[name])

    results[name] = {
        tokenize: time(() => {
            let ruleStack = vsctm.INITIAL

            for (const line of lines) {
                ruleStack = grammar.tokenizeLine(line, ruleStack).ruleStack
            }
        }),
        highlight: time(() => highlighter.codeToHtml(code, { lang: name, theme: theme.name })),
    }

    registry.dispose()
    highlighter.dispose()
}

fs.writeFileSync(output, JSON.stringify(results))
