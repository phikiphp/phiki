// Tokenize the sample of each grammar with vscode-textmate, for meta/compatibility.php.
//
// Usage: node meta/vscode-textmate-samples.js <output.json> <grammar> [grammar ...]
// Writes {grammar: lines}, where each line is a list of [start, end, scopes] tokens with
// offsets in code points.

import fs from 'node:fs'
import path from 'node:path'
import vsctm from 'vscode-textmate'
import oniguruma from 'vscode-oniguruma'

const [output, ...names] = process.argv.slice(2)

if (! output || names.length === 0) {
    console.error('Usage: node meta/vscode-textmate-samples.js <output.json> <grammar> [grammar ...]')
    process.exit(1)
}

const root = path.join(import.meta.dirname, '..')

await oniguruma.loadWASM(fs.readFileSync(path.join(root, 'node_modules/vscode-oniguruma/release/onig.wasm')).buffer)

const grammarsDir = path.join(root, 'resources/grammars/oniguruma')
const grammars = {}
const scopeNames = {}

for (const file of fs.readdirSync(grammarsDir)) {
    const grammar = vsctm.parseRawGrammar(fs.readFileSync(path.join(grammarsDir, file), 'utf8'), file)

    grammars[grammar.scopeName] = grammar
    scopeNames[path.basename(file, '.json')] = grammar.scopeName
}

const registry = new vsctm.Registry({
    onigLib: Promise.resolve({
        createOnigScanner: (patterns) => new oniguruma.OnigScanner(patterns),
        createOnigString: (string) => new oniguruma.OnigString(string),
    }),
    loadGrammar: async (scopeName) => grammars[scopeName] ?? null,
})

const results = {}

for (const name of names) {
    const grammar = await registry.loadGrammar(scopeNames[name])
    const code = fs.readFileSync(path.join(root, `tests/Fixtures/samples/${name}.sample`), 'utf8')
    let ruleStack = vsctm.INITIAL

    results[name] = code.split('\n').map((line) => {
        const result = grammar.tokenizeLine(line, ruleStack)
        ruleStack = result.ruleStack

        // vscode-textmate reports offsets in UTF-16 code units, so count code points instead.
        let offset = 0

        return result.tokens.map((token) => {
            const start = offset
            offset += [...line.substring(token.startIndex, token.endIndex)].length

            return [start, offset, token.scopes.join(' ')]
        })
    })
}

fs.writeFileSync(output, JSON.stringify(results))
