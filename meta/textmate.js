const fs = require("fs");
const path = require("path");
const vsctm = require("vscode-textmate");
const oniguruma = require("vscode-oniguruma");

function readFile(path) {
    return new Promise((resolve, reject) => {
        fs.readFile(path, (error, data) =>
            error ? reject(error) : resolve(data)
        );
    });
}

const wasmBin = fs.readFileSync(
    path.join(__dirname, "./node_modules/vscode-oniguruma/release/onig.wasm")
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
        const map = {
            "source.yaml": "yaml.json",
            "source.shell": "shellscript.json"
        };

        const file = map[scopeName];
        const p = path.join(__dirname, `../languages/${file}`);

        return readFile(p).then(data => {
            return vsctm.parseRawGrammar(data.toString(), p);
        })
    },
});

// Load the JavaScript grammar and any other grammars included by it async.
registry.loadGrammar("source.shell").then((grammar) => {
    const text = fs.readFileSync(path.join(__dirname, "./input.txt")).toString().split("\n");

    let ruleStack = vsctm.INITIAL;

    for (let i = 0; i < text.length; i++) {
        const line = text[i];
        const lineTokens = grammar.tokenizeLine(line, ruleStack);
        // console.log(`\nTokenizing line: ${line}`);
        // for (let j = 0; j < lineTokens.tokens.length; j++) {
        //     const token = lineTokens.tokens[j];
        //     console.log(
        //         ` - token from ${token.startIndex} to ${token.endIndex} ` +
        //             `(${line.substring(token.startIndex, token.endIndex)}) ` +
        //             `with scopes ${token.scopes.join(", ")}`
        //     );
        // }
        
        ruleStack = lineTokens.ruleStack;
    }
});
