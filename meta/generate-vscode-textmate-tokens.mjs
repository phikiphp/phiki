import fs from 'node:fs'
import path from 'node:path'
import vsctm from '../vscode-textmate/release/main.js'
import oniguruma from 'vscode-oniguruma'

const args = process.argv.slice(2);

if (args.length === 0) {
    console.error("Usage: node generate-vscode-textmate-tokens.js <input> <scopeName>");
    process.exit(1);
}

const input = args[0];
const scopeName = args[1];

const wasmBin = fs.readFileSync(
    path.join(import.meta.dirname, "../node_modules/vscode-oniguruma/release/onig.wasm")
).buffer;

const vscodeOnigurumaLib = oniguruma.loadWASM(wasmBin).then(() => {
    return {
        createOnigScanner(patterns) {
            return new oniguruma.OnigScanner(patterns);
        },
        createOnigString(s) {
            return new oniguruma.OnigString(s);
        },
    };
});

const grammarFiles = fs.readdirSync(path.join(import.meta.dirname, "../resources/languages"))
const grammars = {}

grammarFiles.forEach(file => {
    const filePath = path.join(import.meta.dirname, "../resources/languages", file);
    const contents = fs.readFileSync(filePath, 'utf8');
    const grammar = vsctm.parseRawGrammar(contents, file);

    grammars[grammar.scopeName] = grammar;
})

if (! grammars[scopeName]) {
    console.error(`Scope name "${scopeName}" not found in grammars.`);
    process.exit(1);
}

const registry = new vsctm.Registry({
    onigLib: vscodeOnigurumaLib,
    loadGrammar: (scopeName) => {
        if (! grammars[scopeName]) {
            console.log(`Unknown scope name: ${scopeName}`);
            return null;
        }

        return grammars[scopeName];
    },
});

const tokens = []

await registry.loadGrammar(scopeName).then(async (grammar) => {
    const lines = input.split("\n");

    let ruleStack = vsctm.INITIAL;

    lines.forEach(line => {
        const lineTokens = grammar.tokenizeLine(line, ruleStack);
        const processedLineTokens = [];

        lineTokens.tokens.forEach((token) => {
            processedLineTokens.push({
                scopes: token.scopes,
                text: line.substring(token.startIndex, token.endIndex),
                start: token.startIndex,
                end: token.endIndex,
            })
        });

        tokens.push(processedLineTokens);
    })
})

process.stdout.write(JSON.stringify(tokens, null, 4) + "\n")
