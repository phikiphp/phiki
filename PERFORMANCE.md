# Performance

This report times how long Phiki takes to tokenize and highlight the sample of each bundled grammar with each of its regex engines, Oniguruma and PCRE. It compares tokenizing with [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki, and highlighting with [Shiki](https://shiki.style), using its Oniguruma engine.

Regenerate it with `composer performance` after changing an engine, the tokenizer, the highlighter or the HTML output. It needs Node.js and `npm ci`. Timings depend on the machine, so compare reports generated on the same one.

- **Tokenize** is `Phiki::codeToTokens()`, and a call to `tokenizeLine()` for each line in vscode-textmate.
- **Highlight** is `Phiki::codeToHtml()` with the `github-light` theme, rendered to a string, and `codeToHtml()` in Shiki.
- Every implementation uses Phiki's bundled grammars and theme, so they all work from the same input.
- Each time is the median of 5 runs in PHP and 10 in Node.js. Before timing, each sample is tokenized and highlighted once in PHP and 10 times in Node.js, so V8 has optimized the code. Loading and compiling the grammars is not counted.
- **Compared with JavaScript** is how many times longer Phiki takes than vscode-textmate or Shiki for all 227 samples together. **Typical sample** is the geometric mean of that for each sample, so the largest samples don't outweigh the rest.

Generated with PHP 8.4.27 (OPcache disabled, JIT disabled), PCRE2 10.47, Oniguruma 6.9.10 and Node.js v22.23.4, on AMD EPYC 9V74 80-Core Processor.

## Summary

| Task | Implementation | All samples | Compared with JavaScript | Typical sample compared with JavaScript |
| --- | --- | ---: | ---: | ---: |
| Tokenize | vscode-textmate | 1406 ms | – | – |
| Tokenize | Phiki, Oniguruma | 12004 ms | 8.5× | 11.0× |
| Tokenize | Phiki, PCRE | 10341 ms | 7.4× | 9.7× |
| Highlight | Shiki | 1902 ms | – | – |
| Highlight | Phiki, Oniguruma | 12932 ms | 6.8× | 8.1× |
| Highlight | Phiki, PCRE | 11277 ms | 5.9× | 7.3× |

## Grammars

| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| abap | [abap.sample](tests/Fixtures/samples/abap.sample) | 152 | 33.7 ms | 19.4 ms | 8.6 ms | 36.1 ms | 21.8 ms | 9.7 ms |
| actionscript-3 | [actionscript-3.sample](tests/Fixtures/samples/actionscript-3.sample) | 154 | 25.5 ms | 22.7 ms | 2.6 ms | 28.8 ms | 25.8 ms | 4.1 ms |
| ada | [ada.sample](tests/Fixtures/samples/ada.sample) | 226 | 53.9 ms | 49.4 ms | 4.5 ms | 62.6 ms | 56.5 ms | 6.9 ms |
| angular-html | [angular-html.sample](tests/Fixtures/samples/angular-html.sample) | 19 | 7.0 ms | 6.3 ms | 0.3 ms | 7.8 ms | 7.1 ms | 0.9 ms |
| angular-ts | [angular-ts.sample](tests/Fixtures/samples/angular-ts.sample) | 209 | 136 ms | 121 ms | 13.2 ms | 149 ms | 135 ms | 43.1 ms |
| antlers | [antlers.sample](tests/Fixtures/samples/antlers.sample) | 105 | 77.7 ms | 69.1 ms | 5.5 ms | 81.5 ms | 73.0 ms | 6.9 ms |
| apache | [apache.sample](tests/Fixtures/samples/apache.sample) | 148 | 25.9 ms | 20.9 ms | 3.4 ms | 27.4 ms | 22.8 ms | 4.1 ms |
| apex | [apex.sample](tests/Fixtures/samples/apex.sample) | 213 | 87.4 ms | 76.5 ms | 8.0 ms | 92.4 ms | 81.3 ms | 10.1 ms |
| apl | [apl.sample](tests/Fixtures/samples/apl.sample) | 164 | 89.6 ms | 87.9 ms | 6.5 ms | 94.6 ms | 92.5 ms | 8.0 ms |
| applescript | [applescript.sample](tests/Fixtures/samples/applescript.sample) | 126 | 42.3 ms | 36.1 ms | 12.9 ms | 47.1 ms | 41.4 ms | 14.7 ms |
| ara | [ara.sample](tests/Fixtures/samples/ara.sample) | 186 | 33.6 ms | 30.5 ms | 3.4 ms | 37.2 ms | 33.3 ms | 5.0 ms |
| asciidoc | [asciidoc.sample](tests/Fixtures/samples/asciidoc.sample) | 249 | 81.8 ms | 73.7 ms | 7.4 ms | 85.9 ms | 77.9 ms | 4.4 ms |
| asm | [asm.sample](tests/Fixtures/samples/asm.sample) | 238 | 167 ms | 149 ms | 19.7 ms | 170 ms | 153 ms | 21.2 ms |
| astro | [astro.sample](tests/Fixtures/samples/astro.sample) | 122 | 165 ms | 159 ms | 12.2 ms | 175 ms | 172 ms | 13.6 ms |
| awk | [awk.sample](tests/Fixtures/samples/awk.sample) | 25 | 2.5 ms | 2.3 ms | 0.3 ms | 2.9 ms | 2.7 ms | 0.3 ms |
| ballerina | [ballerina.sample](tests/Fixtures/samples/ballerina.sample) | 227 | 95.0 ms | 89.8 ms | 8.5 ms | 103 ms | 100 ms | 11.0 ms |
| bat | [bat.sample](tests/Fixtures/samples/bat.sample) | 82 | 17.2 ms | 15.2 ms | 1.9 ms | 21.4 ms | 16.9 ms | 2.5 ms |
| beancount | [beancount.sample](tests/Fixtures/samples/beancount.sample) | 81 | 13.3 ms | 11.8 ms | 0.7 ms | 15.1 ms | 13.8 ms | 1.7 ms |
| berry | [berry.sample](tests/Fixtures/samples/berry.sample) | 60 | 6.4 ms | 5.8 ms | 0.4 ms | 7.3 ms | 6.7 ms | 0.8 ms |
| bibtex | [bibtex.sample](tests/Fixtures/samples/bibtex.sample) | 29 | 2.1 ms | 2.0 ms | 0.1 ms | 2.6 ms | 2.4 ms | 0.3 ms |
| blade | [blade.sample](tests/Fixtures/samples/blade.sample) | 213 | 126 ms | 111 ms | 12.5 ms | 137 ms | 121 ms | 15.4 ms |
| bsl | [bsl.sample](tests/Fixtures/samples/bsl.sample) | 87 | 23.3 ms | 15.1 ms | 6.7 ms | 24.7 ms | 16.5 ms | 7.4 ms |
| c | [c.sample](tests/Fixtures/samples/c.sample) | 437 | 171 ms | 153 ms | 17.9 ms | 183 ms | 163 ms | 21.7 ms |
| cadence | [cadence.sample](tests/Fixtures/samples/cadence.sample) | 178 | 60.8 ms | 55.5 ms | 4.7 ms | 67.8 ms | 62.5 ms | 6.5 ms |
| cairo | [cairo.sample](tests/Fixtures/samples/cairo.sample) | 156 | 9.3 ms | 8.1 ms | 1.0 ms | 10.6 ms | 9.6 ms | 1.7 ms |
| clarity | [clarity.sample](tests/Fixtures/samples/clarity.sample) | 53 | 6.8 ms | 5.9 ms | 0.4 ms | 8.1 ms | 7.1 ms | 0.7 ms |
| clojure | [clojure.sample](tests/Fixtures/samples/clojure.sample) | 14 | 4.5 ms | 4.1 ms | 0.4 ms | 5.3 ms | 4.9 ms | 0.5 ms |
| cmake | [cmake.sample](tests/Fixtures/samples/cmake.sample) | 139 | 15.8 ms | 10.3 ms | 2.8 ms | 16.9 ms | 11.6 ms | 3.8 ms |
| cobol | [cobol.sample](tests/Fixtures/samples/cobol.sample) | 228 | 138 ms | 82.8 ms | 36.7 ms | 142 ms | 86.7 ms | 38.2 ms |
| codeowners | [codeowners.sample](tests/Fixtures/samples/codeowners.sample) | 8 | 0.4 ms | 0.3 ms | 0.0 ms | 0.5 ms | 0.5 ms | 0.1 ms |
| codeql | [codeql.sample](tests/Fixtures/samples/codeql.sample) | 103 | 41.6 ms | 38.2 ms | 3.6 ms | 44.6 ms | 41.1 ms | 4.5 ms |
| coffee | [coffee.sample](tests/Fixtures/samples/coffee.sample) | 151 | 60.3 ms | 53.2 ms | 5.9 ms | 66.5 ms | 58.5 ms | 7.9 ms |
| common-lisp | [common-lisp.sample](tests/Fixtures/samples/common-lisp.sample) | 29 | 11.9 ms | 6.3 ms | 4.1 ms | 13.0 ms | 7.2 ms | 4.4 ms |
| coq | [coq.sample](tests/Fixtures/samples/coq.sample) | 37 | 5.1 ms | 4.4 ms | 0.4 ms | 5.7 ms | 5.0 ms | 0.8 ms |
| cpp | [cpp.sample](tests/Fixtures/samples/cpp.sample) | 627 | 687 ms | 480 ms | 130 ms | 724 ms | 512 ms | 138 ms |
| crystal | [crystal.sample](tests/Fixtures/samples/crystal.sample) | 184 | 69.9 ms | 66.1 ms | 6.5 ms | 75.3 ms | 72.0 ms | 8.1 ms |
| csharp | [csharp.sample](tests/Fixtures/samples/csharp.sample) | 503 | 233 ms | 207 ms | 20.0 ms | 248 ms | 224 ms | 27.7 ms |
| css | [css.sample](tests/Fixtures/samples/css.sample) | 236 | 65.1 ms | 45.3 ms | 9.7 ms | 72.4 ms | 52.9 ms | 13.3 ms |
| csv | [csv.sample](tests/Fixtures/samples/csv.sample) | 5 | 0.5 ms | 0.4 ms | 0.0 ms | 0.6 ms | 0.6 ms | 0.1 ms |
| cue | [cue.sample](tests/Fixtures/samples/cue.sample) | 192 | 51.0 ms | 47.7 ms | 4.6 ms | 56.8 ms | 52.7 ms | 5.8 ms |
| cypher | [cypher.sample](tests/Fixtures/samples/cypher.sample) | 88 | 21.3 ms | 18.8 ms | 2.3 ms | 24.1 ms | 21.6 ms | 3.2 ms |
| d | [d.sample](tests/Fixtures/samples/d.sample) | 214 | 154 ms | 152 ms | 13.4 ms | 158 ms | 158 ms | 16.6 ms |
| dart | [dart.sample](tests/Fixtures/samples/dart.sample) | 231 | 50.7 ms | 46.9 ms | 4.3 ms | 54.8 ms | 50.8 ms | 6.4 ms |
| dax | [dax.sample](tests/Fixtures/samples/dax.sample) | 20 | 1.9 ms | 1.5 ms | 0.1 ms | 2.3 ms | 1.9 ms | 0.2 ms |
| desktop | [desktop.sample](tests/Fixtures/samples/desktop.sample) | 22 | 1.9 ms | 2.0 ms | 0.4 ms | 2.2 ms | 2.4 ms | 0.5 ms |
| diff | [diff.sample](tests/Fixtures/samples/diff.sample) | 28 | 1.2 ms | 1.1 ms | 0.4 ms | 1.5 ms | 1.3 ms | 0.5 ms |
| djot | [djot.sample](tests/Fixtures/samples/djot.sample) | 126 | 14.2 ms | 12.8 ms | 0.9 ms | 15.9 ms | 14.5 ms | 1.5 ms |
| docker | [docker.sample](tests/Fixtures/samples/docker.sample) | 19 | 0.9 ms | 0.8 ms | 0.1 ms | 1.1 ms | 1.0 ms | 0.2 ms |
| dotenv | [dotenv.sample](tests/Fixtures/samples/dotenv.sample) | 14 | 1.3 ms | 1.2 ms | 0.1 ms | 1.6 ms | 1.5 ms | 0.2 ms |
| dream-maker | [dream-maker.sample](tests/Fixtures/samples/dream-maker.sample) | 124 | 18.9 ms | 17.2 ms | 1.5 ms | 21.2 ms | 19.4 ms | 2.5 ms |
| edge | [edge.sample](tests/Fixtures/samples/edge.sample) | 24 | 10.9 ms | 10.0 ms | 0.7 ms | 11.8 ms | 11.0 ms | 0.9 ms |
| elixir | [elixir.sample](tests/Fixtures/samples/elixir.sample) | 183 | 67.8 ms | 63.2 ms | 6.2 ms | 73.3 ms | 67.9 ms | 8.1 ms |
| elm | [elm.sample](tests/Fixtures/samples/elm.sample) | 66 | 8.3 ms | 7.8 ms | 0.5 ms | 9.4 ms | 8.8 ms | 0.7 ms |
| emacs-lisp | [emacs-lisp.sample](tests/Fixtures/samples/emacs-lisp.sample) | 156 | 266 ms | 96.5 ms | 11.6 ms | 277 ms | 105 ms | 13.2 ms |
| erb | [erb.sample](tests/Fixtures/samples/erb.sample) | 76 | 56.7 ms | 50.7 ms | 5.8 ms | 60.9 ms | 54.7 ms | 7.0 ms |
| erlang | [erlang.sample](tests/Fixtures/samples/erlang.sample) | 201 | 78.0 ms | 71.3 ms | 6.1 ms | 87.0 ms | 81.2 ms | 9.3 ms |
| fennel | [fennel.sample](tests/Fixtures/samples/fennel.sample) | 12 | 3.5 ms | 3.2 ms | 0.3 ms | 4.4 ms | 4.1 ms | 0.4 ms |
| fish | [fish.sample](tests/Fixtures/samples/fish.sample) | 104 | 16.8 ms | 14.0 ms | 1.6 ms | 19.4 ms | 16.3 ms | 2.3 ms |
| fluent | [fluent.sample](tests/Fixtures/samples/fluent.sample) | 27 | 2.3 ms | 2.1 ms | 0.1 ms | 3.0 ms | 2.7 ms | 0.3 ms |
| fortran-fixed-form | [fortran-fixed-form.sample](tests/Fixtures/samples/fortran-fixed-form.sample) | 34 | 15.7 ms | 14.2 ms | 1.9 ms | 16.9 ms | 15.5 ms | 2.1 ms |
| fortran-free-form | [fortran-free-form.sample](tests/Fixtures/samples/fortran-free-form.sample) | 183 | 150 ms | 134 ms | 16.3 ms | 157 ms | 144 ms | 18.8 ms |
| fsharp | [fsharp.sample](tests/Fixtures/samples/fsharp.sample) | 156 | 37.4 ms | 32.6 ms | 4.0 ms | 40.5 ms | 36.0 ms | 5.7 ms |
| gdresource | [gdresource.sample](tests/Fixtures/samples/gdresource.sample) | 95 | 14.4 ms | 12.6 ms | 0.8 ms | 16.0 ms | 14.2 ms | 1.7 ms |
| gdscript | [gdscript.sample](tests/Fixtures/samples/gdscript.sample) | 57 | 15.1 ms | 13.1 ms | 1.5 ms | 16.5 ms | 14.7 ms | 2.0 ms |
| gdshader | [gdshader.sample](tests/Fixtures/samples/gdshader.sample) | 97 | 21.9 ms | 20.3 ms | 1.9 ms | 24.1 ms | 22.1 ms | 2.8 ms |
| genie | [genie.sample](tests/Fixtures/samples/genie.sample) | 109 | 12.0 ms | 10.5 ms | 0.8 ms | 13.6 ms | 12.1 ms | 1.6 ms |
| gherkin | [gherkin.sample](tests/Fixtures/samples/gherkin.sample) | 56 | 4.7 ms | 3.3 ms | 0.6 ms | 5.5 ms | 4.1 ms | 0.9 ms |
| git-commit | [git-commit.sample](tests/Fixtures/samples/git-commit.sample) | 64 | 2.4 ms | 2.2 ms | 0.2 ms | 3.0 ms | 2.8 ms | 0.5 ms |
| git-rebase | [git-rebase.sample](tests/Fixtures/samples/git-rebase.sample) | 37 | 2.8 ms | 2.4 ms | 0.1 ms | 3.3 ms | 3.0 ms | 0.5 ms |
| gleam | [gleam.sample](tests/Fixtures/samples/gleam.sample) | 89 | 12.3 ms | 11.3 ms | 1.0 ms | 13.7 ms | 12.8 ms | 1.9 ms |
| glimmer-js | [glimmer-js.sample](tests/Fixtures/samples/glimmer-js.sample) | 109 | 57.5 ms | 52.6 ms | 4.5 ms | 63.5 ms | 58.9 ms | 5.8 ms |
| glimmer-ts | [glimmer-ts.sample](tests/Fixtures/samples/glimmer-ts.sample) | 119 | 65.6 ms | 60.0 ms | 5.9 ms | 73.1 ms | 66.9 ms | 7.2 ms |
| glsl | [glsl.sample](tests/Fixtures/samples/glsl.sample) | 118 | 52.0 ms | 45.5 ms | 7.6 ms | 54.5 ms | 48.8 ms | 8.8 ms |
| gnuplot | [gnuplot.sample](tests/Fixtures/samples/gnuplot.sample) | 112 | 25.4 ms | 21.8 ms | 1.8 ms | 28.4 ms | 24.9 ms | 3.0 ms |
| go | [go.sample](tests/Fixtures/samples/go.sample) | 613 | 318 ms | 295 ms | 26.0 ms | 333 ms | 309 ms | 36.1 ms |
| graphql | [graphql.sample](tests/Fixtures/samples/graphql.sample) | 168 | 24.3 ms | 22.0 ms | 1.2 ms | 28.4 ms | 25.2 ms | 2.5 ms |
| groovy | [groovy.sample](tests/Fixtures/samples/groovy.sample) | 173 | 53.7 ms | 47.5 ms | 5.3 ms | 59.5 ms | 55.3 ms | 6.8 ms |
| hack | [hack.sample](tests/Fixtures/samples/hack.sample) | 205 | 85.1 ms | 72.4 ms | 9.8 ms | 91.1 ms | 78.9 ms | 12.3 ms |
| haml | [haml.sample](tests/Fixtures/samples/haml.sample) | 110 | 35.3 ms | 33.3 ms | 3.9 ms | 38.6 ms | 36.4 ms | 3.9 ms |
| handlebars | [handlebars.sample](tests/Fixtures/samples/handlebars.sample) | 86 | 29.2 ms | 24.5 ms | 1.9 ms | 32.1 ms | 27.8 ms | 2.8 ms |
| haskell | [haskell.sample](tests/Fixtures/samples/haskell.sample) | 154 | 48.1 ms | 42.4 ms | 9.5 ms | 52.8 ms | 46.4 ms | 11.1 ms |
| haxe | [haxe.sample](tests/Fixtures/samples/haxe.sample) | 172 | 77.2 ms | 69.9 ms | 6.0 ms | 83.6 ms | 77.0 ms | 7.8 ms |
| hcl | [hcl.sample](tests/Fixtures/samples/hcl.sample) | 110 | 24.7 ms | 22.7 ms | 1.5 ms | 27.9 ms | 26.0 ms | 2.5 ms |
| hjson | [hjson.sample](tests/Fixtures/samples/hjson.sample) | 92 | 7.2 ms | 10.9 ms | 0.6 ms | 8.2 ms | 12.6 ms | 1.0 ms |
| hlsl | [hlsl.sample](tests/Fixtures/samples/hlsl.sample) | 167 | 25.0 ms | 20.5 ms | 3.8 ms | 27.9 ms | 22.4 ms | 4.9 ms |
| html | [html.sample](tests/Fixtures/samples/html.sample) | 129 | 88.6 ms | 82.5 ms | 9.8 ms | 98.8 ms | 91.9 ms | 11.9 ms |
| html-derivative | [html-derivative.sample](tests/Fixtures/samples/html-derivative.sample) | 70 | 56.8 ms | 51.7 ms | 2.9 ms | 62.3 ms | 56.5 ms | 6.1 ms |
| http | [http.sample](tests/Fixtures/samples/http.sample) | 94 | 10.5 ms | 9.6 ms | 0.6 ms | 12.5 ms | 11.6 ms | 1.1 ms |
| hurl | [hurl.sample](tests/Fixtures/samples/hurl.sample) | 170 | 13.1 ms | 11.9 ms | 1.4 ms | 15.4 ms | 14.1 ms | 1.5 ms |
| hxml | [hxml.sample](tests/Fixtures/samples/hxml.sample) | 10 | 1.5 ms | 1.3 ms | 0.1 ms | 1.8 ms | 1.6 ms | 0.2 ms |
| hy | [hy.sample](tests/Fixtures/samples/hy.sample) | 33 | 0.9 ms | 2.9 ms | 0.3 ms | 1.1 ms | 3.5 ms | 0.5 ms |
| imba | [imba.sample](tests/Fixtures/samples/imba.sample) | 164 | 69.9 ms | 63.8 ms | 25.0 ms | 75.5 ms | 68.7 ms | 26.1 ms |
| ini | [ini.sample](tests/Fixtures/samples/ini.sample) | 13 | 0.8 ms | 0.7 ms | 0.0 ms | 1.0 ms | 1.0 ms | 0.1 ms |
| java | [java.sample](tests/Fixtures/samples/java.sample) | 280 | 107 ms | 97.6 ms | 11.0 ms | 117 ms | 110 ms | 14.2 ms |
| javascript | [javascript.sample](tests/Fixtures/samples/javascript.sample) | 710 | 415 ms | 373 ms | 38.0 ms | 449 ms | 398 ms | 158 ms |
| jinja | [jinja.sample](tests/Fixtures/samples/jinja.sample) | 64 | 14.3 ms | 13.3 ms | 0.9 ms | 16.4 ms | 15.6 ms | 1.7 ms |
| jison | [jison.sample](tests/Fixtures/samples/jison.sample) | 61 | 13.3 ms | 12.6 ms | 1.0 ms | 14.5 ms | 13.8 ms | 1.5 ms |
| json | [json.sample](tests/Fixtures/samples/json.sample) | 51 | 6.8 ms | 6.3 ms | 0.3 ms | 8.0 ms | 7.6 ms | 0.6 ms |
| json5 | [json5.sample](tests/Fixtures/samples/json5.sample) | 42 | 8.0 ms | 7.5 ms | 0.4 ms | 9.1 ms | 8.5 ms | 0.6 ms |
| jsonc | [jsonc.sample](tests/Fixtures/samples/jsonc.sample) | 35 | 6.3 ms | 6.0 ms | 0.3 ms | 7.4 ms | 6.9 ms | 0.5 ms |
| jsonl | [jsonl.sample](tests/Fixtures/samples/jsonl.sample) | 32 | 5.1 ms | 4.8 ms | 0.2 ms | 5.9 ms | 5.5 ms | 0.4 ms |
| jsonnet | [jsonnet.sample](tests/Fixtures/samples/jsonnet.sample) | 34 | 3.9 ms | 3.5 ms | 0.2 ms | 4.4 ms | 4.0 ms | 0.5 ms |
| jssm | [jssm.sample](tests/Fixtures/samples/jssm.sample) | 22 | 8.6 ms | 8.1 ms | 0.5 ms | 9.3 ms | 8.8 ms | 0.7 ms |
| jsx | [jsx.sample](tests/Fixtures/samples/jsx.sample) | 719 | 407 ms | 365 ms | 36.7 ms | 440 ms | 402 ms | 46.6 ms |
| julia | [julia.sample](tests/Fixtures/samples/julia.sample) | 157 | 49.0 ms | 42.9 ms | 4.2 ms | 52.5 ms | 46.4 ms | 5.8 ms |
| kdl | [kdl.sample](tests/Fixtures/samples/kdl.sample) | 76 | 7.9 ms | 7.3 ms | 0.6 ms | 9.0 ms | 8.3 ms | 0.9 ms |
| kotlin | [kotlin.sample](tests/Fixtures/samples/kotlin.sample) | 190 | 29.7 ms | 27.8 ms | 2.4 ms | 32.2 ms | 30.7 ms | 4.0 ms |
| kusto | [kusto.sample](tests/Fixtures/samples/kusto.sample) | 72 | 20.1 ms | 16.8 ms | 2.2 ms | 22.0 ms | 18.7 ms | 3.0 ms |
| latex | [latex.sample](tests/Fixtures/samples/latex.sample) | 204 | 63.3 ms | 55.9 ms | 24.0 ms | 68.5 ms | 61.1 ms | 25.7 ms |
| lean | [lean.sample](tests/Fixtures/samples/lean.sample) | 128 | 9.9 ms | 8.2 ms | 1.0 ms | 10.8 ms | 9.2 ms | 1.6 ms |
| less | [less.sample](tests/Fixtures/samples/less.sample) | 212 | 117 ms | 107 ms | 12.2 ms | 129 ms | 117 ms | 15.8 ms |
| liquid | [liquid.sample](tests/Fixtures/samples/liquid.sample) | 121 | 29.4 ms | 25.8 ms | 2.3 ms | 34.0 ms | 30.8 ms | 3.2 ms |
| llvm | [llvm.sample](tests/Fixtures/samples/llvm.sample) | 26 | 6.2 ms | 4.7 ms | 0.5 ms | 6.9 ms | 5.3 ms | 0.8 ms |
| log | [log.sample](tests/Fixtures/samples/log.sample) | 47 | 11.3 ms | 7.3 ms | 1.6 ms | 12.8 ms | 9.4 ms | 2.2 ms |
| logo | [logo.sample](tests/Fixtures/samples/logo.sample) | 22 | 1.6 ms | 1.3 ms | 0.1 ms | 1.9 ms | 1.6 ms | 0.2 ms |
| lua | [lua.sample](tests/Fixtures/samples/lua.sample) | 204 | 48.2 ms | 42.7 ms | 3.3 ms | 52.0 ms | 46.8 ms | 5.3 ms |
| luau | [luau.sample](tests/Fixtures/samples/luau.sample) | 97 | 25.3 ms | 23.5 ms | 1.9 ms | 27.9 ms | 25.7 ms | 2.9 ms |
| make | [make.sample](tests/Fixtures/samples/make.sample) | 117 | 16.8 ms | 15.5 ms | 0.9 ms | 19.2 ms | 18.1 ms | 1.7 ms |
| maml | [maml.sample](tests/Fixtures/samples/maml.sample) | 29 | 3.4 ms | 3.1 ms | 0.1 ms | 4.1 ms | 3.8 ms | 0.2 ms |
| markdown | [markdown.sample](tests/Fixtures/samples/markdown.sample) | 449 | 118 ms | 101 ms | 14.3 ms | 128 ms | 112 ms | 8.1 ms |
| marko | [marko.sample](tests/Fixtures/samples/marko.sample) | 125 | 84.9 ms | 69.6 ms | 9.9 ms | 91.2 ms | 74.9 ms | 11.6 ms |
| matlab | [matlab.sample](tests/Fixtures/samples/matlab.sample) | 115 | 17.4 ms | 15.7 ms | 1.3 ms | 19.8 ms | 17.9 ms | 2.1 ms |
| mdc | [mdc.sample](tests/Fixtures/samples/mdc.sample) | 34 | 7.2 ms | 6.2 ms | 21.2 ms | 8.0 ms | 6.9 ms | 20.4 ms |
| mdx | [mdx.sample](tests/Fixtures/samples/mdx.sample) | 135 | 51.3 ms | 44.1 ms | 3.8 ms | 56.1 ms | 49.2 ms | 4.7 ms |
| mermaid | [mermaid.sample](tests/Fixtures/samples/mermaid.sample) | 224 | 21.3 ms | 16.6 ms | 2.6 ms | 24.1 ms | 19.1 ms | 4.2 ms |
| mipsasm | [mipsasm.sample](tests/Fixtures/samples/mipsasm.sample) | 25 | 2.3 ms | 1.9 ms | 0.1 ms | 2.7 ms | 2.3 ms | 0.2 ms |
| mojo | [mojo.sample](tests/Fixtures/samples/mojo.sample) | 209 | 88.0 ms | 80.0 ms | 8.9 ms | 95.5 ms | 87.2 ms | 10.4 ms |
| move | [move.sample](tests/Fixtures/samples/move.sample) | 151 | 27.7 ms | 25.6 ms | 2.0 ms | 32.1 ms | 29.8 ms | 3.1 ms |
| narrat | [narrat.sample](tests/Fixtures/samples/narrat.sample) | 92 | 10.0 ms | 8.8 ms | 1.0 ms | 11.1 ms | 10.0 ms | 1.5 ms |
| neon | [neon.sample](tests/Fixtures/samples/neon.sample) | 97 | 9.5 ms | 8.6 ms | 0.6 ms | 11.1 ms | 10.1 ms | 0.9 ms |
| nextflow | [nextflow.sample](tests/Fixtures/samples/nextflow.sample) | 138 | 3.7 ms | 3.3 ms | 0.3 ms | 4.5 ms | 4.0 ms | 0.8 ms |
| nginx | [nginx.sample](tests/Fixtures/samples/nginx.sample) | 213 | 47.4 ms | 42.1 ms | 2.4 ms | 51.0 ms | 45.8 ms | 3.5 ms |
| nim | [nim.sample](tests/Fixtures/samples/nim.sample) | 177 | 45.4 ms | 38.8 ms | 6.4 ms | 49.1 ms | 42.6 ms | 7.8 ms |
| nix | [nix.sample](tests/Fixtures/samples/nix.sample) | 45 | 9.9 ms | 9.0 ms | 0.5 ms | 10.6 ms | 9.7 ms | 0.8 ms |
| nushell | [nushell.sample](tests/Fixtures/samples/nushell.sample) | 68 | 24.2 ms | 21.6 ms | 1.8 ms | 27.1 ms | 24.2 ms | 2.9 ms |
| objective-c | [objective-c.sample](tests/Fixtures/samples/objective-c.sample) | 177 | 90.8 ms | 74.4 ms | 10.5 ms | 96.8 ms | 81.2 ms | 11.8 ms |
| objective-cpp | [objective-cpp.sample](tests/Fixtures/samples/objective-cpp.sample) | 229 | 143 ms | 125 ms | 18.5 ms | 152 ms | 134 ms | 21.2 ms |
| ocaml | [ocaml.sample](tests/Fixtures/samples/ocaml.sample) | 140 | 63.0 ms | 54.0 ms | 6.3 ms | 67.1 ms | 59.6 ms | 8.3 ms |
| pascal | [pascal.sample](tests/Fixtures/samples/pascal.sample) | 110 | 9.6 ms | 6.8 ms | 1.7 ms | 10.5 ms | 7.8 ms | 2.3 ms |
| perl | [perl.sample](tests/Fixtures/samples/perl.sample) | 170 | 53.2 ms | 48.4 ms | 4.7 ms | 58.4 ms | 54.3 ms | 6.5 ms |
| php | [php.sample](tests/Fixtures/samples/php.sample) | 465 | 289 ms | 239 ms | 34.8 ms | 311 ms | 262 ms | 42.3 ms |
| pkl | [pkl.sample](tests/Fixtures/samples/pkl.sample) | 138 | 27.0 ms | 24.5 ms | 2.0 ms | 29.5 ms | 27.0 ms | 2.9 ms |
| plsql | [plsql.sample](tests/Fixtures/samples/plsql.sample) | 121 | 27.3 ms | 20.0 ms | 6.1 ms | 28.9 ms | 22.0 ms | 7.4 ms |
| polar | [polar.sample](tests/Fixtures/samples/polar.sample) | 150 | 18.3 ms | 17.0 ms | 1.2 ms | 20.8 ms | 19.5 ms | 2.2 ms |
| postcss | [postcss.sample](tests/Fixtures/samples/postcss.sample) | 141 | 19.6 ms | 17.0 ms | 1.5 ms | 21.5 ms | 19.2 ms | 2.4 ms |
| powerquery | [powerquery.sample](tests/Fixtures/samples/powerquery.sample) | 23 | 5.5 ms | 5.0 ms | 0.4 ms | 6.1 ms | 5.6 ms | 0.6 ms |
| powershell | [powershell.sample](tests/Fixtures/samples/powershell.sample) | 146 | 41.7 ms | 37.3 ms | 4.5 ms | 47.3 ms | 42.6 ms | 5.8 ms |
| prisma | [prisma.sample](tests/Fixtures/samples/prisma.sample) | 42 | 3.3 ms | 3.0 ms | 0.2 ms | 4.1 ms | 3.9 ms | 0.4 ms |
| prolog | [prolog.sample](tests/Fixtures/samples/prolog.sample) | 180 | 45.8 ms | 40.3 ms | 3.1 ms | 49.6 ms | 44.1 ms | 4.6 ms |
| proto | [proto.sample](tests/Fixtures/samples/proto.sample) | 45 | 3.5 ms | 3.2 ms | 0.2 ms | 4.2 ms | 3.9 ms | 0.4 ms |
| pug | [pug.sample](tests/Fixtures/samples/pug.sample) | 87 | 33.2 ms | 29.4 ms | 49.0 ms | 36.1 ms | 32.5 ms | 50.8 ms |
| puppet | [puppet.sample](tests/Fixtures/samples/puppet.sample) | 138 | 21.0 ms | 18.8 ms | 1.4 ms | 23.9 ms | 21.5 ms | 2.4 ms |
| purescript | [purescript.sample](tests/Fixtures/samples/purescript.sample) | 82 | 21.4 ms | 19.5 ms | 2.4 ms | 23.7 ms | 21.7 ms | 2.3 ms |
| python | [python.sample](tests/Fixtures/samples/python.sample) | 348 | 150 ms | 135 ms | 17.1 ms | 162 ms | 148 ms | 22.6 ms |
| qml | [qml.sample](tests/Fixtures/samples/qml.sample) | 27 | 13.0 ms | 12.0 ms | 1.1 ms | 14.2 ms | 13.2 ms | 1.2 ms |
| qmldir | [qmldir.sample](tests/Fixtures/samples/qmldir.sample) | 8 | 0.5 ms | 0.4 ms | 0.0 ms | 0.7 ms | 0.6 ms | 0.1 ms |
| qss | [qss.sample](tests/Fixtures/samples/qss.sample) | 37 | 3.5 ms | 3.0 ms | 0.2 ms | 4.1 ms | 3.6 ms | 0.5 ms |
| r | [r.sample](tests/Fixtures/samples/r.sample) | 106 | 66.0 ms | 46.2 ms | 7.9 ms | 69.5 ms | 50.2 ms | 9.1 ms |
| racket | [racket.sample](tests/Fixtures/samples/racket.sample) | 127 | 196 ms | 124 ms | 8.1 ms | 199 ms | 129 ms | 9.6 ms |
| raku | [raku.sample](tests/Fixtures/samples/raku.sample) | 134 | 20.9 ms | 16.6 ms | 2.2 ms | 22.6 ms | 18.4 ms | 2.9 ms |
| razor | [razor.sample](tests/Fixtures/samples/razor.sample) | 138 | 74.2 ms | 65.5 ms | 18.9 ms | 80.6 ms | 72.4 ms | 19.4 ms |
| reg | [reg.sample](tests/Fixtures/samples/reg.sample) | 27 | 1.4 ms | 1.3 ms | 0.1 ms | 1.8 ms | 1.6 ms | 0.2 ms |
| regexp | [regexp.sample](tests/Fixtures/samples/regexp.sample) | 30 | 9.9 ms | 9.1 ms | 0.7 ms | 11.8 ms | 11.0 ms | 1.1 ms |
| rel | [rel.sample](tests/Fixtures/samples/rel.sample) | 66 | 5.8 ms | 4.7 ms | 0.7 ms | 6.5 ms | 5.4 ms | 1.1 ms |
| riscv | [riscv.sample](tests/Fixtures/samples/riscv.sample) | 31 | 4.8 ms | 4.1 ms | 0.3 ms | 5.4 ms | 4.7 ms | 0.4 ms |
| rosmsg | [rosmsg.sample](tests/Fixtures/samples/rosmsg.sample) | 67 | 9.4 ms | 8.5 ms | 0.4 ms | 10.4 ms | 9.5 ms | 0.8 ms |
| rst | [rst.sample](tests/Fixtures/samples/rst.sample) | 149 | 21.3 ms | 19.5 ms | 1.5 ms | 23.1 ms | 21.2 ms | 1.5 ms |
| ruby | [ruby.sample](tests/Fixtures/samples/ruby.sample) | 329 | 175 ms | 165 ms | 18.8 ms | 185 ms | 174 ms | 22.1 ms |
| rust | [rust.sample](tests/Fixtures/samples/rust.sample) | 154 | 58.6 ms | 55.5 ms | 5.1 ms | 62.7 ms | 59.9 ms | 6.9 ms |
| sas | [sas.sample](tests/Fixtures/samples/sas.sample) | 75 | 16.5 ms | 11.3 ms | 3.0 ms | 18.1 ms | 12.9 ms | 3.5 ms |
| sass | [sass.sample](tests/Fixtures/samples/sass.sample) | 104 | 20.2 ms | 18.0 ms | 1.4 ms | 22.7 ms | 20.4 ms | 2.1 ms |
| scala | [scala.sample](tests/Fixtures/samples/scala.sample) | 218 | 102 ms | 92.2 ms | 7.7 ms | 108 ms | 98.1 ms | 10.6 ms |
| scheme | [scheme.sample](tests/Fixtures/samples/scheme.sample) | 27 | 3.6 ms | 3.1 ms | 0.2 ms | 4.2 ms | 3.7 ms | 0.4 ms |
| scss | [scss.sample](tests/Fixtures/samples/scss.sample) | 229 | 96.1 ms | 65.7 ms | 19.3 ms | 103 ms | 73.3 ms | 21.7 ms |
| sdbl | [sdbl.sample](tests/Fixtures/samples/sdbl.sample) | 22 | 2.1 ms | 1.6 ms | 0.6 ms | 2.5 ms | 1.9 ms | 0.7 ms |
| shaderlab | [shaderlab.sample](tests/Fixtures/samples/shaderlab.sample) | 61 | 8.0 ms | 6.4 ms | 0.9 ms | 8.6 ms | 7.1 ms | 1.2 ms |
| shellscript | [shellscript.sample](tests/Fixtures/samples/shellscript.sample) | 200 | 76.8 ms | 68.6 ms | 7.8 ms | 86.7 ms | 78.6 ms | 9.3 ms |
| shellsession | [shellsession.sample](tests/Fixtures/samples/shellsession.sample) | 14 | 1.6 ms | 1.2 ms | 0.0 ms | 1.9 ms | 1.4 ms | 0.5 ms |
| smalltalk | [smalltalk.sample](tests/Fixtures/samples/smalltalk.sample) | 12 | 2.3 ms | 2.2 ms | 0.2 ms | 2.9 ms | 2.8 ms | 0.2 ms |
| solidity | [solidity.sample](tests/Fixtures/samples/solidity.sample) | 173 | 64.2 ms | 62.2 ms | 5.7 ms | 68.0 ms | 65.6 ms | 7.1 ms |
| soy | [soy.sample](tests/Fixtures/samples/soy.sample) | 89 | 21.3 ms | 18.6 ms | 1.5 ms | 23.4 ms | 20.6 ms | 2.2 ms |
| sparql | [sparql.sample](tests/Fixtures/samples/sparql.sample) | 8 | 1.0 ms | 0.8 ms | 0.1 ms | 1.2 ms | 1.1 ms | 0.2 ms |
| splunk | [splunk.sample](tests/Fixtures/samples/splunk.sample) | 16 | 3.0 ms | 2.5 ms | 0.4 ms | 3.4 ms | 3.0 ms | 0.6 ms |
| sql | [sql.sample](tests/Fixtures/samples/sql.sample) | 135 | 50.7 ms | 32.1 ms | 10.2 ms | 53.2 ms | 34.9 ms | 11.7 ms |
| ssh-config | [ssh-config.sample](tests/Fixtures/samples/ssh-config.sample) | 48 | 3.9 ms | 3.3 ms | 0.2 ms | 4.4 ms | 3.8 ms | 0.4 ms |
| stata | [stata.sample](tests/Fixtures/samples/stata.sample) | 143 | 63.6 ms | 51.5 ms | 6.0 ms | 67.2 ms | 55.4 ms | 7.4 ms |
| stylus | [stylus.sample](tests/Fixtures/samples/stylus.sample) | 133 | 38.6 ms | 32.4 ms | 3.0 ms | 42.1 ms | 36.0 ms | 3.9 ms |
| svelte | [svelte.sample](tests/Fixtures/samples/svelte.sample) | 137 | 215 ms | 228 ms | 15.3 ms | 226 ms | 239 ms | 17.3 ms |
| swift | [swift.sample](tests/Fixtures/samples/swift.sample) | 502 | 314 ms | 257 ms | 28.2 ms | 330 ms | 275 ms | 35.5 ms |
| system-verilog | [system-verilog.sample](tests/Fixtures/samples/system-verilog.sample) | 184 | 57.9 ms | 53.0 ms | 8.7 ms | 62.0 ms | 57.4 ms | 10.4 ms |
| systemd | [systemd.sample](tests/Fixtures/samples/systemd.sample) | 22 | 1.3 ms | 1.1 ms | 0.1 ms | 1.6 ms | 1.4 ms | 0.2 ms |
| talonscript | [talonscript.sample](tests/Fixtures/samples/talonscript.sample) | 120 | 13.9 ms | 12.7 ms | 0.8 ms | 15.8 ms | 14.5 ms | 1.4 ms |
| tasl | [tasl.sample](tests/Fixtures/samples/tasl.sample) | 26 | 2.0 ms | 1.9 ms | 0.1 ms | 2.5 ms | 2.3 ms | 0.2 ms |
| tcl | [tcl.sample](tests/Fixtures/samples/tcl.sample) | 162 | 13.8 ms | 12.1 ms | 1.0 ms | 15.8 ms | 14.0 ms | 2.1 ms |
| templ | [templ.sample](tests/Fixtures/samples/templ.sample) | 113 | 22.9 ms | 34.6 ms | 2.7 ms | 26.6 ms | 39.3 ms | 3.6 ms |
| terraform | [terraform.sample](tests/Fixtures/samples/terraform.sample) | 162 | 38.0 ms | 31.6 ms | 2.2 ms | 39.5 ms | 35.8 ms | 3.4 ms |
| tex | [tex.sample](tests/Fixtures/samples/tex.sample) | 30 | 2.9 ms | 2.6 ms | 0.2 ms | 3.6 ms | 3.2 ms | 0.4 ms |
| toml | [toml.sample](tests/Fixtures/samples/toml.sample) | 26 | 3.3 ms | 3.0 ms | 0.1 ms | 4.0 ms | 3.6 ms | 0.3 ms |
| ts-tags | [ts-tags.sample](tests/Fixtures/samples/ts-tags.sample) | 50 | 10.0 ms | 8.8 ms | 0.7 ms | 11.0 ms | 9.8 ms | 4.5 ms |
| tsv | [tsv.sample](tests/Fixtures/samples/tsv.sample) | 6 | 0.5 ms | 0.5 ms | 0.0 ms | 0.7 ms | 0.6 ms | 0.1 ms |
| tsx | [tsx.sample](tests/Fixtures/samples/tsx.sample) | 518 | 323 ms | 288 ms | 32.4 ms | 350 ms | 323 ms | 42.3 ms |
| turtle | [turtle.sample](tests/Fixtures/samples/turtle.sample) | 16 | 1.8 ms | 1.7 ms | 0.1 ms | 2.3 ms | 2.1 ms | 0.3 ms |
| twig | [twig.sample](tests/Fixtures/samples/twig.sample) | 120 | 42.6 ms | 37.0 ms | 4.2 ms | 47.5 ms | 41.5 ms | 5.6 ms |
| txt | [txt.sample](tests/Fixtures/samples/txt.sample) | 33 | 0.3 ms | 0.3 ms | 0.1 ms | 0.5 ms | 0.5 ms | 0.2 ms |
| typescript | [typescript.sample](tests/Fixtures/samples/typescript.sample) | 530 | 330 ms | 301 ms | 36.5 ms | 364 ms | 331 ms | 101 ms |
| typespec | [typespec.sample](tests/Fixtures/samples/typespec.sample) | 36 | 7.1 ms | 6.5 ms | 0.8 ms | 8.4 ms | 7.8 ms | 0.6 ms |
| typst | [typst.sample](tests/Fixtures/samples/typst.sample) | 28 | 5.9 ms | 5.6 ms | 0.4 ms | 6.6 ms | 6.5 ms | 0.6 ms |
| v | [v.sample](tests/Fixtures/samples/v.sample) | 203 | 54.1 ms | 48.1 ms | 4.2 ms | 57.5 ms | 51.0 ms | 5.6 ms |
| vala | [vala.sample](tests/Fixtures/samples/vala.sample) | 171 | 19.3 ms | 16.7 ms | 1.5 ms | 21.4 ms | 19.0 ms | 2.8 ms |
| vb | [vb.sample](tests/Fixtures/samples/vb.sample) | 39 | 8.3 ms | 6.9 ms | 0.9 ms | 9.0 ms | 7.6 ms | 1.2 ms |
| verilog | [verilog.sample](tests/Fixtures/samples/verilog.sample) | 31 | 3.0 ms | 2.8 ms | 0.2 ms | 3.6 ms | 3.3 ms | 0.4 ms |
| vhdl | [vhdl.sample](tests/Fixtures/samples/vhdl.sample) | 216 | 55.9 ms | 48.9 ms | 35.8 ms | 61.0 ms | 53.4 ms | 41.4 ms |
| viml | [viml.sample](tests/Fixtures/samples/viml.sample) | 126 | 32.2 ms | 24.3 ms | 4.1 ms | 34.6 ms | 26.3 ms | 5.0 ms |
| vue | [vue.sample](tests/Fixtures/samples/vue.sample) | 188 | 79.0 ms | 68.3 ms | 8.0 ms | 89.6 ms | 81.6 ms | 17.3 ms |
| vue-html | [vue-html.sample](tests/Fixtures/samples/vue-html.sample) | 71 | 34.2 ms | 30.1 ms | 3.3 ms | 37.7 ms | 33.6 ms | 4.7 ms |
| vue-vine | [vue-vine.sample](tests/Fixtures/samples/vue-vine.sample) | 253 | 143 ms | 129 ms | 16.1 ms | 161 ms | 150 ms | 21.0 ms |
| vyper | [vyper.sample](tests/Fixtures/samples/vyper.sample) | 192 | 84.3 ms | 74.0 ms | 8.8 ms | 88.8 ms | 78.2 ms | 10.3 ms |
| wasm | [wasm.sample](tests/Fixtures/samples/wasm.sample) | 175 | 58.4 ms | 54.0 ms | 4.5 ms | 62.4 ms | 57.3 ms | 6.7 ms |
| wgsl | [wgsl.sample](tests/Fixtures/samples/wgsl.sample) | 19 | 4.4 ms | 4.1 ms | 0.3 ms | 5.0 ms | 4.7 ms | 0.5 ms |
| wikitext | [wikitext.sample](tests/Fixtures/samples/wikitext.sample) | 33 | 19.4 ms | 17.6 ms | 2.3 ms | 20.6 ms | 18.7 ms | 2.9 ms |
| wit | [wit.sample](tests/Fixtures/samples/wit.sample) | 59 | 9.6 ms | 9.0 ms | 0.4 ms | 11.9 ms | 11.3 ms | 0.8 ms |
| wolfram | [wolfram.sample](tests/Fixtures/samples/wolfram.sample) | 176 | 457 ms | 427 ms | 50.1 ms | 469 ms | 439 ms | 54.4 ms |
| xml | [xml.sample](tests/Fixtures/samples/xml.sample) | 123 | 16.0 ms | 14.6 ms | 0.8 ms | 17.7 ms | 16.2 ms | 1.8 ms |
| xsl | [xsl.sample](tests/Fixtures/samples/xsl.sample) | 66 | 7.4 ms | 6.8 ms | 0.3 ms | 8.6 ms | 8.0 ms | 0.7 ms |
| yaml | [yaml.sample](tests/Fixtures/samples/yaml.sample) | 87 | 19.0 ms | 15.9 ms | 1.7 ms | 21.1 ms | 18.0 ms | 1.9 ms |
| zenscript | [zenscript.sample](tests/Fixtures/samples/zenscript.sample) | 44 | 4.8 ms | 4.2 ms | 0.4 ms | 5.5 ms | 5.0 ms | 0.7 ms |
| zig | [zig.sample](tests/Fixtures/samples/zig.sample) | 26 | 7.0 ms | 6.5 ms | 0.5 ms | 7.7 ms | 7.2 ms | 0.8 ms |
