const fs = require('fs')
const path = require('path')
const vsctm = require('vscode-textmate')
const oniguruma = require('vscode-oniguruma')

function readFile(path) {
    return new Promise((resolve, reject) => {
        fs.readFile(path, (error, data) =>
            error ? reject(error) : resolve(data)
        );
    });
}

const wasmBin = fs.readFileSync(
    path.join(__dirname, "../node_modules/vscode-oniguruma/release/onig.wasm")
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

const grammarFiles = fs.readdirSync(path.join(__dirname, "../resources/languages"))
const grammars = {}

grammarFiles.forEach(file => {
    const filePath = path.join(__dirname, "../resources/languages", file);
    const contents = fs.readFileSync(filePath, 'utf8');
    const grammar = vsctm.parseRawGrammar(contents, file);

    grammars[grammar.scopeName] = grammar;
})

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

registry.loadGrammar("source.cpp").then(async (grammar) => {
    const text = await readFile(path.join(__dirname, '../resources/samples/cpp.sample')).then(file => file.toString().split("\n"));
    let ruleStack = vsctm.INITIAL;
    for (let i = 0; i < text.length; i++) {
        const line = text[i];
        const lineTokens = grammar.tokenizeLine(line, ruleStack);
        // console.log(`\nTokenizing line: ${line}`);
        for (let j = 0; j < lineTokens.tokens.length; j++) {
            const token = lineTokens.tokens[j];
            console.log(
                ` - token from ${token.startIndex} to ${token.endIndex} ` +
                    `(${line.substring(token.startIndex, token.endIndex)}) ` +
                    `with scopes ${token.scopes.join(", ")}`
            );
        }
        ruleStack = lineTokens.ruleStack;
    }
});
