const process = require('node:process');
const fs = require('node:fs');
const path = require('node:path');
const oniguruma = require('vscode-oniguruma');
const vsctm = require('vscode-textmate');

const sample = process.argv[2];
const scope = process.argv[3];
const scopeMap = JSON.parse(process.argv[4]);

function readFile(path) {
    return new Promise((resolve, reject) => {
        fs.readFile(path, (error, data) =>
            error ? reject(error) : resolve(data)
        );
    });
}

const wasmBin = fs.readFileSync(
    path.join(__dirname, "../../node_modules/vscode-oniguruma/release/onig.wasm")
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

const registry = new vsctm.Registry({
    onigLib: vscodeOnigurumaLib,
    loadGrammar: (scopeName) => {
        if (! scopeMap[scopeName]) {
            console.error(`Unknown scope name: ${scopeName}`);
            process.exit(1);
        }

        return readFile(scopeMap[scopeName]).then(data => {
            return vsctm.parseRawGrammar(data.toString(), scopeMap[scopeName]);
        })
    },
});

const tokens = []

registry.loadGrammar(scope).then(async grammar => {
    const text = await readFile(sample);
    const lines = text.toString().split(/\r?\n/);

    let ruleStack = vsctm.INITIAL;
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const lineTokens = grammar.tokenizeLine(line, ruleStack);

        tokens.push(
            lineTokens.tokens.map(lineToken => ({
                scopes: lineToken.scopes,
                text: line.substring(lineToken.startIndex, lineToken.endIndex),
                start: lineToken.startIndex,
                end: lineToken.endIndex,
            }))
        )

        ruleStack = lineTokens.ruleStack;
    }

    console.log(JSON.stringify(tokens));
})
