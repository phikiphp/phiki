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

const registry = new vsctm.Registry({
    onigLib: vscodeOnigurumaLib,
    loadGrammar: (scopeName) => {
        if (scopeName === "source.svelte") {
            // https://github.com/textmate/javascript.tmbundle/blob/master/Syntaxes/JavaScript.plist
            return readFile(path.join(__dirname, "../resources/languages/svelte.json")).then((data) =>
                vsctm.parseRawGrammar(data.toString(), 'svelte.json')
            );
        }
        if (scopeName === 'source.js') {
            return readFile(path.join(__dirname, "../resources/languages/javascript.json")).then((data) =>
                vsctm.parseRawGrammar(data.toString(), 'javascript.json')
            );
        }
        if (scopeName === 'source.shell') {
            return readFile(
                path.join(__dirname, "../resources/languages/shellscript.json")
            ).then((data) =>
                vsctm.parseRawGrammar(data.toString(), "shellscript.json")
            );
        }
        console.log(`Unknown scope name: ${scopeName}`);
        return null;
    },
});

registry.loadGrammar("source.shell").then(async (grammar) => {
    const text = await readFile(path.join(__dirname, '../resources/samples/shellscript.sample')).then(file => file.toString().split("\n"));
    let ruleStack = vsctm.INITIAL;
    for (let i = 0; i < text.length; i++) {
        const line = text[i];
        const lineTokens = grammar.tokenizeLine(line, ruleStack);
        // console.log(`\nTokenizing line: ${line}`);
        for (let j = 0; j < lineTokens.tokens.length; j++) {
            const token = lineTokens.tokens[j];
            // console.log(
            //     ` - token from ${token.startIndex} to ${token.endIndex} ` +
            //         `(${line.substring(token.startIndex, token.endIndex)}) ` +
            //         `with scopes ${token.scopes.join(", ")}`
            // );
        }
        ruleStack = lineTokens.ruleStack;
    }
});
