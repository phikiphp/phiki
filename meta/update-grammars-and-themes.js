import { pascalCase } from "change-case";
import { grammars } from "tm-grammars";
import { themes } from "tm-themes";
import fs from 'node:fs'
import { basePath } from './utils.js'

console.log("Updating grammars...");

const cases = {}
const aliases = {}
const scopeNames = {}
const exclusions = ["source.bicep", "text.xml"];

grammars.forEach(grammar => {
    if (grammar.injectTo) {
        console.warn(`Skipping grammar ${grammar.name} as it is an injection grammar.`);
        return;
    }

    if (exclusions.includes(grammar.scopeName)) {
        console.warn(`Skipping grammar ${grammar.name} as it is in the exclusions list.`);
        return;
    }
    
    cases[pascalCase(grammar.name)] = grammar.name;
    aliases[pascalCase(grammar.name)] = grammar.aliases ?? [];
    scopeNames[pascalCase(grammar.name)] = grammar.scopeName;

    fs.copyFileSync(basePath(`node_modules/tm-grammars/grammars/${grammar.name}.json`), basePath(`resources/grammars/${grammar.name}.json`));
})

console.log(`Found ${Object.keys(cases).length} grammars.`);

let grammarsStub = fs.readFileSync(basePath('meta/stubs/Grammar.stub.php'), { encoding: 'utf-8' });

const casesString = Object.entries(cases)
    .map(([key, value]) => `case ${key} = '${value}';`)
    .join('\n    ');

grammarsStub = grammarsStub.replace('{cases}', casesString);

const aliasesString = Object.entries(aliases)
    .map(([key, value]) => `self::${key} => ${JSON.stringify(value)},`)
    .join('\n            ');

grammarsStub = grammarsStub.replace('{aliases}', aliasesString);

const scopeNamesString = Object.entries(scopeNames)
    .map(([key, value]) => `self::${key} => '${value}',`)
    .join('\n            ');

grammarsStub = grammarsStub.replace('{scopeNames}', scopeNamesString);

console.log("Writing grammars stub...");

fs.writeFileSync(basePath('src/Grammar/Grammar.php'), grammarsStub);

console.log("Grammars updated successfully.");

const themeCases = {}

themes.forEach(theme => {
    themeCases[pascalCase(theme.name)] = theme.name;

    fs.copyFileSync(basePath(`node_modules/tm-themes/themes/${theme.name}.json`), basePath(`resources/themes/${theme.name}.json`));
})

console.log(`Found ${Object.keys(themeCases).length} themes.`);

let themesStub = fs.readFileSync(basePath('meta/stubs/Theme.stub.php'), { encoding: 'utf-8' });

const themeCasesString = Object.entries(themeCases)
    .map(([key, value]) => `case ${key} = '${value}';`)
    .join('\n    ');

themesStub = themesStub.replace('{cases}', themeCasesString);

console.log("Writing themes stub...");
fs.writeFileSync(basePath('src/Theme/Theme.php'), themesStub);
console.log("Themes updated successfully.");
