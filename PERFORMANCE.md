# Performance

This report times how long Phiki takes to tokenize and highlight the sample of each bundled grammar with each of its regex engines, Oniguruma and PCRE. It compares tokenizing with [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki, and highlighting with [Shiki](https://shiki.style), using its Oniguruma engine.

Regenerate it with `composer performance` after changing an engine, the tokenizer, the highlighter or the HTML output. It needs Node.js and `npm ci`. Timings depend on the machine, so compare reports generated on the same one.

- **Tokenize** is `Phiki::codeToTokens()`, and a call to `tokenizeLine()` for each line in vscode-textmate.
- **Highlight** is `Phiki::codeToHtml()` with the `github-light` theme, rendered to a string, and `codeToHtml()` in Shiki.
- Every implementation uses Phiki's bundled grammars and theme, so they all work from the same input.
- Each time is the median of 5 runs in PHP and 10 in Node.js. Before timing, each sample is tokenized and highlighted once in PHP and 10 times in Node.js, so V8 has optimized the code. Loading and compiling the grammars is not counted.
- **Compared with JavaScript** is how many times longer Phiki takes than vscode-textmate or Shiki for all 227 samples together. **Typical sample** is the geometric mean of that for each sample, so the largest samples don't outweigh the rest.

Generated with PHP 8.4.27 (OPcache disabled, JIT disabled), PCRE2 10.42, Oniguruma 6.9.9 and Node.js v22.23.4, on AMD EPYC 9V74 80-Core Processor.

## Summary

| Task | Implementation | All samples | Compared with JavaScript | Typical sample compared with JavaScript |
| --- | --- | ---: | ---: | ---: |
| Tokenize | vscode-textmate | 1767 ms | – | – |
| Tokenize | Phiki, Oniguruma | 25637 ms | 14.5× | 16.7× |
| Tokenize | Phiki, PCRE | 23638 ms | 13.4× | 15.2× |
| Highlight | Shiki | 2379 ms | – | – |
| Highlight | Phiki, Oniguruma | 45160 ms | 19.0× | 21.7× |
| Highlight | Phiki, PCRE | 43338 ms | 18.2× | 20.8× |

## Grammars

| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| abap | [abap.sample](tests/Fixtures/samples/abap.sample) | 152 | 55.2 ms | 38.3 ms | 11.2 ms | 78.7 ms | 59.1 ms | 12.6 ms |
| actionscript-3 | [actionscript-3.sample](tests/Fixtures/samples/actionscript-3.sample) | 154 | 46.7 ms | 42.2 ms | 3.4 ms | 114 ms | 114 ms | 5.3 ms |
| ada | [ada.sample](tests/Fixtures/samples/ada.sample) | 226 | 105 ms | 96.1 ms | 5.8 ms | 321 ms | 311 ms | 8.6 ms |
| angular-html | [angular-html.sample](tests/Fixtures/samples/angular-html.sample) | 19 | 13.0 ms | 12.1 ms | 0.4 ms | 37.4 ms | 36.3 ms | 1.1 ms |
| angular-ts | [angular-ts.sample](tests/Fixtures/samples/angular-ts.sample) | 209 | 262 ms | 238 ms | 16.7 ms | 630 ms | 608 ms | 54.3 ms |
| antlers | [antlers.sample](tests/Fixtures/samples/antlers.sample) | 105 | 263 ms | 251 ms | 6.9 ms | 336 ms | 326 ms | 8.8 ms |
| apache | [apache.sample](tests/Fixtures/samples/apache.sample) | 148 | 46.2 ms | 40.3 ms | 4.3 ms | 64.5 ms | 58.3 ms | 5.5 ms |
| apex | [apex.sample](tests/Fixtures/samples/apex.sample) | 213 | 171 ms | 150 ms | 9.8 ms | 227 ms | 207 ms | 12.7 ms |
| apl | [apl.sample](tests/Fixtures/samples/apl.sample) | 164 | 197 ms | 190 ms | 8.3 ms | 274 ms | 264 ms | 10.6 ms |
| applescript | [applescript.sample](tests/Fixtures/samples/applescript.sample) | 126 | 87.2 ms | 77.9 ms | 16.6 ms | 251 ms | 241 ms | 18.7 ms |
| ara | [ara.sample](tests/Fixtures/samples/ara.sample) | 186 | 63.1 ms | 56.5 ms | 4.5 ms | 91.5 ms | 83.7 ms | 6.4 ms |
| asciidoc | [asciidoc.sample](tests/Fixtures/samples/asciidoc.sample) | 249 | 175 ms | 170 ms | 8.8 ms | 291 ms | 285 ms | 5.3 ms |
| asm | [asm.sample](tests/Fixtures/samples/asm.sample) | 238 | 346 ms | 320 ms | 26.0 ms | 384 ms | 359 ms | 27.7 ms |
| astro | [astro.sample](tests/Fixtures/samples/astro.sample) | 122 | 786 ms | 770 ms | 13.9 ms | 1113 ms | 1094 ms | 15.8 ms |
| awk | [awk.sample](tests/Fixtures/samples/awk.sample) | 25 | 4.7 ms | 4.5 ms | 0.2 ms | 9.6 ms | 9.5 ms | 0.4 ms |
| ballerina | [ballerina.sample](tests/Fixtures/samples/ballerina.sample) | 227 | 190 ms | 184 ms | 10.8 ms | 417 ms | 401 ms | 13.8 ms |
| bat | [bat.sample](tests/Fixtures/samples/bat.sample) | 82 | 33.1 ms | 28.2 ms | 2.4 ms | 77.1 ms | 71.5 ms | 3.2 ms |
| beancount | [beancount.sample](tests/Fixtures/samples/beancount.sample) | 81 | 21.2 ms | 18.8 ms | 0.9 ms | 58.5 ms | 56.2 ms | 2.1 ms |
| berry | [berry.sample](tests/Fixtures/samples/berry.sample) | 60 | 11.2 ms | 9.9 ms | 0.5 ms | 18.6 ms | 17.5 ms | 1.0 ms |
| bibtex | [bibtex.sample](tests/Fixtures/samples/bibtex.sample) | 29 | 3.4 ms | 3.1 ms | 0.1 ms | 10.5 ms | 10.1 ms | 0.3 ms |
| blade | [blade.sample](tests/Fixtures/samples/blade.sample) | 213 | 346 ms | 279 ms | 15.9 ms | 575 ms | 554 ms | 18.5 ms |
| bsl | [bsl.sample](tests/Fixtures/samples/bsl.sample) | 87 | 40.0 ms | 30.0 ms | 8.9 ms | 58.0 ms | 47.6 ms | 9.8 ms |
| c | [c.sample](tests/Fixtures/samples/c.sample) | 437 | 352 ms | 331 ms | 21.5 ms | 575 ms | 551 ms | 27.2 ms |
| cadence | [cadence.sample](tests/Fixtures/samples/cadence.sample) | 178 | 122 ms | 112 ms | 6.1 ms | 319 ms | 309 ms | 8.2 ms |
| cairo | [cairo.sample](tests/Fixtures/samples/cairo.sample) | 156 | 19.9 ms | 17.8 ms | 1.2 ms | 45.8 ms | 43.4 ms | 2.2 ms |
| clarity | [clarity.sample](tests/Fixtures/samples/clarity.sample) | 53 | 12.2 ms | 10.8 ms | 0.5 ms | 41.2 ms | 40.2 ms | 0.9 ms |
| clojure | [clojure.sample](tests/Fixtures/samples/clojure.sample) | 14 | 9.0 ms | 8.3 ms | 0.5 ms | 30.4 ms | 29.5 ms | 0.7 ms |
| cmake | [cmake.sample](tests/Fixtures/samples/cmake.sample) | 139 | 25.5 ms | 18.8 ms | 3.5 ms | 36.7 ms | 30.6 ms | 4.5 ms |
| cobol | [cobol.sample](tests/Fixtures/samples/cobol.sample) | 228 | 244 ms | 170 ms | 48.2 ms | 285 ms | 210 ms | 49.6 ms |
| codeowners | [codeowners.sample](tests/Fixtures/samples/codeowners.sample) | 8 | 0.5 ms | 0.5 ms | 0.0 ms | 2.2 ms | 2.1 ms | 0.1 ms |
| codeql | [codeql.sample](tests/Fixtures/samples/codeql.sample) | 103 | 87.3 ms | 81.3 ms | 4.5 ms | 159 ms | 153 ms | 5.4 ms |
| coffee | [coffee.sample](tests/Fixtures/samples/coffee.sample) | 151 | 117 ms | 107 ms | 7.6 ms | 205 ms | 194 ms | 9.7 ms |
| common-lisp | [common-lisp.sample](tests/Fixtures/samples/common-lisp.sample) | 29 | 19.9 ms | 12.6 ms | 5.2 ms | 30.3 ms | 23.1 ms | 5.7 ms |
| coq | [coq.sample](tests/Fixtures/samples/coq.sample) | 37 | 9.4 ms | 8.2 ms | 0.6 ms | 16.7 ms | 15.5 ms | 1.0 ms |
| cpp | [cpp.sample](tests/Fixtures/samples/cpp.sample) | 627 | 1186 ms | 968 ms | 158 ms | 2196 ms | 1942 ms | 166 ms |
| crystal | [crystal.sample](tests/Fixtures/samples/crystal.sample) | 184 | 143 ms | 136 ms | 8.3 ms | 209 ms | 204 ms | 10.0 ms |
| csharp | [csharp.sample](tests/Fixtures/samples/csharp.sample) | 503 | 455 ms | 412 ms | 23.5 ms | 611 ms | 563 ms | 31.9 ms |
| css | [css.sample](tests/Fixtures/samples/css.sample) | 236 | 104 ms | 78.9 ms | 11.6 ms | 267 ms | 241 ms | 14.6 ms |
| csv | [csv.sample](tests/Fixtures/samples/csv.sample) | 5 | 0.7 ms | 0.6 ms | 0.0 ms | 2.5 ms | 2.5 ms | 0.1 ms |
| cue | [cue.sample](tests/Fixtures/samples/cue.sample) | 192 | 93.2 ms | 86.5 ms | 6.0 ms | 195 ms | 187 ms | 7.2 ms |
| cypher | [cypher.sample](tests/Fixtures/samples/cypher.sample) | 88 | 38.2 ms | 33.8 ms | 3.1 ms | 66.1 ms | 61.3 ms | 4.3 ms |
| d | [d.sample](tests/Fixtures/samples/d.sample) | 214 | 345 ms | 336 ms | 16.6 ms | 402 ms | 407 ms | 20.0 ms |
| dart | [dart.sample](tests/Fixtures/samples/dart.sample) | 231 | 93.3 ms | 84.5 ms | 5.6 ms | 135 ms | 127 ms | 8.0 ms |
| dax | [dax.sample](tests/Fixtures/samples/dax.sample) | 20 | 3.2 ms | 2.8 ms | 0.2 ms | 6.8 ms | 6.3 ms | 0.3 ms |
| desktop | [desktop.sample](tests/Fixtures/samples/desktop.sample) | 22 | 3.2 ms | 3.4 ms | 0.5 ms | 6.2 ms | 7.8 ms | 0.6 ms |
| diff | [diff.sample](tests/Fixtures/samples/diff.sample) | 28 | 2.1 ms | 1.9 ms | 0.5 ms | 5.1 ms | 4.9 ms | 0.6 ms |
| djot | [djot.sample](tests/Fixtures/samples/djot.sample) | 126 | 28.2 ms | 26.6 ms | 1.2 ms | 69.6 ms | 68.3 ms | 1.9 ms |
| docker | [docker.sample](tests/Fixtures/samples/docker.sample) | 19 | 1.5 ms | 1.3 ms | 0.1 ms | 3.7 ms | 3.4 ms | 0.2 ms |
| dotenv | [dotenv.sample](tests/Fixtures/samples/dotenv.sample) | 14 | 2.0 ms | 1.9 ms | 0.1 ms | 5.2 ms | 5.1 ms | 0.2 ms |
| dream-maker | [dream-maker.sample](tests/Fixtures/samples/dream-maker.sample) | 124 | 35.0 ms | 30.7 ms | 1.9 ms | 61.1 ms | 59.2 ms | 3.1 ms |
| edge | [edge.sample](tests/Fixtures/samples/edge.sample) | 24 | 21.4 ms | 20.2 ms | 0.8 ms | 49.0 ms | 49.0 ms | 1.1 ms |
| elixir | [elixir.sample](tests/Fixtures/samples/elixir.sample) | 183 | 135 ms | 131 ms | 7.9 ms | 204 ms | 200 ms | 9.9 ms |
| elm | [elm.sample](tests/Fixtures/samples/elm.sample) | 66 | 15.7 ms | 14.5 ms | 0.6 ms | 34.4 ms | 33.1 ms | 0.9 ms |
| emacs-lisp | [emacs-lisp.sample](tests/Fixtures/samples/emacs-lisp.sample) | 156 | 396 ms | 286 ms | 14.2 ms | 625 ms | 517 ms | 16.2 ms |
| erb | [erb.sample](tests/Fixtures/samples/erb.sample) | 76 | 128 ms | 120 ms | 7.0 ms | 243 ms | 235 ms | 8.1 ms |
| erlang | [erlang.sample](tests/Fixtures/samples/erlang.sample) | 201 | 151 ms | 139 ms | 7.7 ms | 410 ms | 399 ms | 10.3 ms |
| fennel | [fennel.sample](tests/Fixtures/samples/fennel.sample) | 12 | 6.8 ms | 6.2 ms | 0.3 ms | 32.6 ms | 32.0 ms | 0.5 ms |
| fish | [fish.sample](tests/Fixtures/samples/fish.sample) | 104 | 29.0 ms | 25.2 ms | 2.0 ms | 57.8 ms | 54.3 ms | 2.9 ms |
| fluent | [fluent.sample](tests/Fixtures/samples/fluent.sample) | 27 | 3.6 ms | 3.2 ms | 0.1 ms | 11.8 ms | 11.3 ms | 0.4 ms |
| fortran-fixed-form | [fortran-fixed-form.sample](tests/Fixtures/samples/fortran-fixed-form.sample) | 34 | 40.5 ms | 37.2 ms | 2.4 ms | 68.8 ms | 65.7 ms | 2.7 ms |
| fortran-free-form | [fortran-free-form.sample](tests/Fixtures/samples/fortran-free-form.sample) | 183 | 619 ms | 613 ms | 20.6 ms | 944 ms | 915 ms | 22.4 ms |
| fsharp | [fsharp.sample](tests/Fixtures/samples/fsharp.sample) | 156 | 69.0 ms | 60.5 ms | 5.1 ms | 109 ms | 102 ms | 7.3 ms |
| gdresource | [gdresource.sample](tests/Fixtures/samples/gdresource.sample) | 95 | 24.3 ms | 21.2 ms | 1.1 ms | 39.0 ms | 36.1 ms | 2.2 ms |
| gdscript | [gdscript.sample](tests/Fixtures/samples/gdscript.sample) | 57 | 31.0 ms | 28.5 ms | 2.0 ms | 61.8 ms | 58.8 ms | 2.5 ms |
| gdshader | [gdshader.sample](tests/Fixtures/samples/gdshader.sample) | 97 | 41.9 ms | 38.3 ms | 2.4 ms | 67.1 ms | 63.3 ms | 3.7 ms |
| genie | [genie.sample](tests/Fixtures/samples/genie.sample) | 109 | 21.0 ms | 19.0 ms | 1.1 ms | 36.0 ms | 34.0 ms | 2.1 ms |
| gherkin | [gherkin.sample](tests/Fixtures/samples/gherkin.sample) | 56 | 7.4 ms | 5.9 ms | 0.7 ms | 14.9 ms | 13.3 ms | 1.2 ms |
| git-commit | [git-commit.sample](tests/Fixtures/samples/git-commit.sample) | 64 | 4.1 ms | 3.7 ms | 0.3 ms | 13.8 ms | 13.5 ms | 0.6 ms |
| git-rebase | [git-rebase.sample](tests/Fixtures/samples/git-rebase.sample) | 37 | 6.3 ms | 5.4 ms | 0.1 ms | 15.1 ms | 14.4 ms | 0.6 ms |
| gleam | [gleam.sample](tests/Fixtures/samples/gleam.sample) | 89 | 23.4 ms | 20.9 ms | 1.3 ms | 37.9 ms | 35.6 ms | 2.4 ms |
| glimmer-js | [glimmer-js.sample](tests/Fixtures/samples/glimmer-js.sample) | 109 | 129 ms | 120 ms | 5.9 ms | 277 ms | 267 ms | 7.1 ms |
| glimmer-ts | [glimmer-ts.sample](tests/Fixtures/samples/glimmer-ts.sample) | 119 | 165 ms | 143 ms | 7.2 ms | 351 ms | 348 ms | 8.8 ms |
| glsl | [glsl.sample](tests/Fixtures/samples/glsl.sample) | 118 | 124 ms | 115 ms | 9.4 ms | 178 ms | 167 ms | 11.1 ms |
| gnuplot | [gnuplot.sample](tests/Fixtures/samples/gnuplot.sample) | 112 | 47.7 ms | 42.9 ms | 2.4 ms | 93.7 ms | 88.5 ms | 4.0 ms |
| go | [go.sample](tests/Fixtures/samples/go.sample) | 613 | 639 ms | 600 ms | 33.7 ms | 757 ms | 715 ms | 42.8 ms |
| graphql | [graphql.sample](tests/Fixtures/samples/graphql.sample) | 168 | 41.5 ms | 37.3 ms | 1.5 ms | 125 ms | 121 ms | 2.9 ms |
| groovy | [groovy.sample](tests/Fixtures/samples/groovy.sample) | 173 | 107 ms | 98.7 ms | 6.8 ms | 282 ms | 274 ms | 8.9 ms |
| hack | [hack.sample](tests/Fixtures/samples/hack.sample) | 205 | 157 ms | 142 ms | 12.7 ms | 261 ms | 246 ms | 15.3 ms |
| haml | [haml.sample](tests/Fixtures/samples/haml.sample) | 110 | 82.4 ms | 76.5 ms | 5.0 ms | 159 ms | 155 ms | 5.2 ms |
| handlebars | [handlebars.sample](tests/Fixtures/samples/handlebars.sample) | 86 | 56.6 ms | 50.5 ms | 2.5 ms | 121 ms | 115 ms | 3.6 ms |
| haskell | [haskell.sample](tests/Fixtures/samples/haskell.sample) | 154 | 96.0 ms | 87.8 ms | 12.4 ms | 173 ms | 164 ms | 14.0 ms |
| haxe | [haxe.sample](tests/Fixtures/samples/haxe.sample) | 172 | 161 ms | 149 ms | 7.8 ms | 328 ms | 308 ms | 10.0 ms |
| hcl | [hcl.sample](tests/Fixtures/samples/hcl.sample) | 110 | 45.2 ms | 41.7 ms | 1.9 ms | 108 ms | 105 ms | 3.2 ms |
| hjson | [hjson.sample](tests/Fixtures/samples/hjson.sample) | 92 | 12.7 ms | 18.0 ms | 0.8 ms | 30.0 ms | 79.3 ms | 1.4 ms |
| hlsl | [hlsl.sample](tests/Fixtures/samples/hlsl.sample) | 167 | 46.8 ms | 40.6 ms | 4.9 ms | 67.4 ms | 60.8 ms | 6.3 ms |
| html | [html.sample](tests/Fixtures/samples/html.sample) | 129 | 197 ms | 187 ms | 12.3 ms | 505 ms | 495 ms | 15.3 ms |
| html-derivative | [html-derivative.sample](tests/Fixtures/samples/html-derivative.sample) | 70 | 125 ms | 116 ms | 3.7 ms | 289 ms | 280 ms | 7.6 ms |
| http | [http.sample](tests/Fixtures/samples/http.sample) | 94 | 20.6 ms | 18.7 ms | 0.8 ms | 65.2 ms | 63.6 ms | 1.5 ms |
| hurl | [hurl.sample](tests/Fixtures/samples/hurl.sample) | 170 | 22.5 ms | 20.4 ms | 0.9 ms | 54.5 ms | 52.9 ms | 1.9 ms |
| hxml | [hxml.sample](tests/Fixtures/samples/hxml.sample) | 10 | 3.6 ms | 3.3 ms | 0.1 ms | 7.2 ms | 7.0 ms | 0.2 ms |
| hy | [hy.sample](tests/Fixtures/samples/hy.sample) | 33 | 1.4 ms | 4.8 ms | 0.3 ms | 3.7 ms | 10.1 ms | 0.6 ms |
| imba | [imba.sample](tests/Fixtures/samples/imba.sample) | 164 | 139 ms | 126 ms | 31.4 ms | 219 ms | 208 ms | 33.8 ms |
| ini | [ini.sample](tests/Fixtures/samples/ini.sample) | 13 | 1.2 ms | 1.2 ms | 0.0 ms | 4.4 ms | 4.3 ms | 0.1 ms |
| java | [java.sample](tests/Fixtures/samples/java.sample) | 280 | 213 ms | 190 ms | 14.1 ms | 460 ms | 452 ms | 17.1 ms |
| javascript | [javascript.sample](tests/Fixtures/samples/javascript.sample) | 710 | 812 ms | 736 ms | 49.1 ms | 1475 ms | 1402 ms | 201 ms |
| jinja | [jinja.sample](tests/Fixtures/samples/jinja.sample) | 64 | 25.2 ms | 22.8 ms | 1.2 ms | 51.1 ms | 48.8 ms | 2.1 ms |
| jison | [jison.sample](tests/Fixtures/samples/jison.sample) | 61 | 42.8 ms | 41.1 ms | 1.4 ms | 63.8 ms | 62.1 ms | 1.8 ms |
| json | [json.sample](tests/Fixtures/samples/json.sample) | 51 | 10.9 ms | 10.3 ms | 0.4 ms | 36.9 ms | 36.2 ms | 0.8 ms |
| json5 | [json5.sample](tests/Fixtures/samples/json5.sample) | 42 | 13.6 ms | 12.5 ms | 0.5 ms | 31.9 ms | 31.1 ms | 0.8 ms |
| jsonc | [jsonc.sample](tests/Fixtures/samples/jsonc.sample) | 35 | 11.0 ms | 10.1 ms | 0.4 ms | 36.2 ms | 35.1 ms | 0.6 ms |
| jsonl | [jsonl.sample](tests/Fixtures/samples/jsonl.sample) | 32 | 8.3 ms | 7.8 ms | 0.3 ms | 21.3 ms | 20.9 ms | 0.5 ms |
| jsonnet | [jsonnet.sample](tests/Fixtures/samples/jsonnet.sample) | 34 | 7.4 ms | 6.5 ms | 0.5 ms | 12.4 ms | 11.5 ms | 0.6 ms |
| jssm | [jssm.sample](tests/Fixtures/samples/jssm.sample) | 22 | 17.1 ms | 15.7 ms | 0.7 ms | 23.3 ms | 22.2 ms | 0.8 ms |
| jsx | [jsx.sample](tests/Fixtures/samples/jsx.sample) | 719 | 790 ms | 728 ms | 48.0 ms | 1705 ms | 1635 ms | 58.7 ms |
| julia | [julia.sample](tests/Fixtures/samples/julia.sample) | 157 | 97.5 ms | 87.3 ms | 5.4 ms | 143 ms | 132 ms | 7.2 ms |
| kdl | [kdl.sample](tests/Fixtures/samples/kdl.sample) | 76 | 14.4 ms | 13.1 ms | 0.6 ms | 25.7 ms | 24.4 ms | 1.2 ms |
| kotlin | [kotlin.sample](tests/Fixtures/samples/kotlin.sample) | 190 | 55.4 ms | 53.7 ms | 3.1 ms | 84.2 ms | 85.7 ms | 5.1 ms |
| kusto | [kusto.sample](tests/Fixtures/samples/kusto.sample) | 72 | 37.5 ms | 33.3 ms | 2.9 ms | 66.9 ms | 62.8 ms | 3.9 ms |
| latex | [latex.sample](tests/Fixtures/samples/latex.sample) | 204 | 135 ms | 148 ms | 30.8 ms | 267 ms | 277 ms | 33.4 ms |
| lean | [lean.sample](tests/Fixtures/samples/lean.sample) | 128 | 17.3 ms | 14.9 ms | 1.3 ms | 26.9 ms | 24.3 ms | 2.1 ms |
| less | [less.sample](tests/Fixtures/samples/less.sample) | 212 | 262 ms | 231 ms | 16.4 ms | 561 ms | 539 ms | 20.1 ms |
| liquid | [liquid.sample](tests/Fixtures/samples/liquid.sample) | 121 | 79.5 ms | 74.7 ms | 2.9 ms | 171 ms | 165 ms | 4.2 ms |
| llvm | [llvm.sample](tests/Fixtures/samples/llvm.sample) | 26 | 10.4 ms | 8.3 ms | 0.7 ms | 16.0 ms | 14.1 ms | 1.1 ms |
| log | [log.sample](tests/Fixtures/samples/log.sample) | 47 | 20.5 ms | 14.2 ms | 2.3 ms | 29.8 ms | 22.9 ms | 2.8 ms |
| logo | [logo.sample](tests/Fixtures/samples/logo.sample) | 22 | 2.5 ms | 2.1 ms | 0.1 ms | 6.0 ms | 5.6 ms | 0.2 ms |
| lua | [lua.sample](tests/Fixtures/samples/lua.sample) | 204 | 89.5 ms | 80.3 ms | 4.1 ms | 139 ms | 132 ms | 6.4 ms |
| luau | [luau.sample](tests/Fixtures/samples/luau.sample) | 97 | 50.2 ms | 46.3 ms | 2.4 ms | 84.4 ms | 79.7 ms | 3.5 ms |
| make | [make.sample](tests/Fixtures/samples/make.sample) | 117 | 29.1 ms | 26.1 ms | 1.1 ms | 71.6 ms | 68.9 ms | 2.1 ms |
| maml | [maml.sample](tests/Fixtures/samples/maml.sample) | 29 | 5.6 ms | 5.4 ms | 0.2 ms | 24.8 ms | 24.6 ms | 0.3 ms |
| markdown | [markdown.sample](tests/Fixtures/samples/markdown.sample) | 449 | 308 ms | 297 ms | 16.8 ms | 637 ms | 631 ms | 9.8 ms |
| marko | [marko.sample](tests/Fixtures/samples/marko.sample) | 125 | 200 ms | 178 ms | 12.3 ms | 326 ms | 299 ms | 13.9 ms |
| matlab | [matlab.sample](tests/Fixtures/samples/matlab.sample) | 115 | 35.3 ms | 32.8 ms | 1.7 ms | 85.8 ms | 83.3 ms | 2.7 ms |
| mdc | [mdc.sample](tests/Fixtures/samples/mdc.sample) | 34 | 16.8 ms | 15.8 ms | 25.3 ms | 36.8 ms | 37.4 ms | 26.4 ms |
| mdx | [mdx.sample](tests/Fixtures/samples/mdx.sample) | 135 | 123 ms | 139 ms | 4.8 ms | 249 ms | 269 ms | 5.9 ms |
| mermaid | [mermaid.sample](tests/Fixtures/samples/mermaid.sample) | 224 | 34.3 ms | 27.2 ms | 3.2 ms | 58.1 ms | 51.5 ms | 5.2 ms |
| mipsasm | [mipsasm.sample](tests/Fixtures/samples/mipsasm.sample) | 25 | 3.8 ms | 3.3 ms | 0.2 ms | 8.3 ms | 7.8 ms | 0.3 ms |
| mojo | [mojo.sample](tests/Fixtures/samples/mojo.sample) | 209 | 177 ms | 159 ms | 11.1 ms | 313 ms | 297 ms | 13.2 ms |
| move | [move.sample](tests/Fixtures/samples/move.sample) | 151 | 53.9 ms | 48.8 ms | 2.6 ms | 167 ms | 162 ms | 4.1 ms |
| narrat | [narrat.sample](tests/Fixtures/samples/narrat.sample) | 92 | 18.8 ms | 16.7 ms | 1.3 ms | 32.2 ms | 30.2 ms | 2.0 ms |
| neon | [neon.sample](tests/Fixtures/samples/neon.sample) | 97 | 17.4 ms | 15.6 ms | 0.7 ms | 36.7 ms | 35.0 ms | 1.2 ms |
| nextflow | [nextflow.sample](tests/Fixtures/samples/nextflow.sample) | 138 | 6.1 ms | 5.4 ms | 0.5 ms | 13.5 ms | 12.9 ms | 1.1 ms |
| nginx | [nginx.sample](tests/Fixtures/samples/nginx.sample) | 213 | 92.5 ms | 86.2 ms | 3.1 ms | 155 ms | 148 ms | 4.5 ms |
| nim | [nim.sample](tests/Fixtures/samples/nim.sample) | 177 | 92.2 ms | 80.0 ms | 8.0 ms | 138 ms | 126 ms | 10.1 ms |
| nix | [nix.sample](tests/Fixtures/samples/nix.sample) | 45 | 19.3 ms | 17.8 ms | 0.6 ms | 28.9 ms | 27.2 ms | 0.9 ms |
| nushell | [nushell.sample](tests/Fixtures/samples/nushell.sample) | 68 | 45.6 ms | 41.0 ms | 2.2 ms | 128 ms | 122 ms | 3.0 ms |
| objective-c | [objective-c.sample](tests/Fixtures/samples/objective-c.sample) | 177 | 181 ms | 165 ms | 13.1 ms | 373 ms | 359 ms | 15.6 ms |
| objective-cpp | [objective-cpp.sample](tests/Fixtures/samples/objective-cpp.sample) | 229 | 301 ms | 282 ms | 23.8 ms | 591 ms | 573 ms | 26.2 ms |
| ocaml | [ocaml.sample](tests/Fixtures/samples/ocaml.sample) | 140 | 119 ms | 109 ms | 8.2 ms | 158 ms | 146 ms | 10.6 ms |
| pascal | [pascal.sample](tests/Fixtures/samples/pascal.sample) | 110 | 15.3 ms | 12.0 ms | 2.4 ms | 24.9 ms | 21.0 ms | 2.9 ms |
| perl | [perl.sample](tests/Fixtures/samples/perl.sample) | 170 | 108 ms | 99.7 ms | 6.2 ms | 201 ms | 195 ms | 8.2 ms |
| php | [php.sample](tests/Fixtures/samples/php.sample) | 465 | 586 ms | 519 ms | 44.2 ms | 1129 ms | 1049 ms | 50.9 ms |
| pkl | [pkl.sample](tests/Fixtures/samples/pkl.sample) | 138 | 50.9 ms | 45.6 ms | 2.5 ms | 77.2 ms | 72.0 ms | 3.6 ms |
| plsql | [plsql.sample](tests/Fixtures/samples/plsql.sample) | 121 | 48.6 ms | 38.6 ms | 8.0 ms | 71.3 ms | 61.8 ms | 9.3 ms |
| polar | [polar.sample](tests/Fixtures/samples/polar.sample) | 150 | 32.3 ms | 29.1 ms | 1.5 ms | 62.1 ms | 59.7 ms | 2.7 ms |
| postcss | [postcss.sample](tests/Fixtures/samples/postcss.sample) | 141 | 35.0 ms | 31.6 ms | 1.8 ms | 57.9 ms | 54.4 ms | 3.1 ms |
| powerquery | [powerquery.sample](tests/Fixtures/samples/powerquery.sample) | 23 | 10.0 ms | 9.0 ms | 0.5 ms | 18.1 ms | 17.0 ms | 0.7 ms |
| powershell | [powershell.sample](tests/Fixtures/samples/powershell.sample) | 146 | 80.5 ms | 72.1 ms | 5.7 ms | 197 ms | 189 ms | 7.3 ms |
| prisma | [prisma.sample](tests/Fixtures/samples/prisma.sample) | 42 | 5.8 ms | 5.3 ms | 0.2 ms | 20.0 ms | 19.4 ms | 0.5 ms |
| prolog | [prolog.sample](tests/Fixtures/samples/prolog.sample) | 180 | 82.0 ms | 73.7 ms | 4.1 ms | 130 ms | 122 ms | 5.9 ms |
| proto | [proto.sample](tests/Fixtures/samples/proto.sample) | 45 | 6.2 ms | 5.6 ms | 0.2 ms | 14.6 ms | 14.0 ms | 0.5 ms |
| pug | [pug.sample](tests/Fixtures/samples/pug.sample) | 87 | 83.3 ms | 78.7 ms | 62.1 ms | 153 ms | 149 ms | 64.5 ms |
| puppet | [puppet.sample](tests/Fixtures/samples/puppet.sample) | 138 | 38.7 ms | 35.2 ms | 1.8 ms | 86.8 ms | 82.5 ms | 3.1 ms |
| purescript | [purescript.sample](tests/Fixtures/samples/purescript.sample) | 82 | 37.9 ms | 34.4 ms | 2.0 ms | 67.7 ms | 66.0 ms | 2.9 ms |
| python | [python.sample](tests/Fixtures/samples/python.sample) | 348 | 298 ms | 298 ms | 22.5 ms | 555 ms | 518 ms | 28.0 ms |
| qml | [qml.sample](tests/Fixtures/samples/qml.sample) | 27 | 31.2 ms | 29.9 ms | 1.4 ms | 48.5 ms | 46.1 ms | 1.6 ms |
| qmldir | [qmldir.sample](tests/Fixtures/samples/qmldir.sample) | 8 | 0.9 ms | 0.8 ms | 0.0 ms | 2.9 ms | 2.9 ms | 0.1 ms |
| qss | [qss.sample](tests/Fixtures/samples/qss.sample) | 37 | 6.1 ms | 5.5 ms | 0.3 ms | 12.1 ms | 11.7 ms | 0.6 ms |
| r | [r.sample](tests/Fixtures/samples/r.sample) | 106 | 120 ms | 96.3 ms | 10.3 ms | 180 ms | 155 ms | 11.5 ms |
| racket | [racket.sample](tests/Fixtures/samples/racket.sample) | 127 | 250 ms | 203 ms | 11.1 ms | 313 ms | 267 ms | 12.4 ms |
| raku | [raku.sample](tests/Fixtures/samples/raku.sample) | 134 | 38.7 ms | 32.3 ms | 2.7 ms | 58.9 ms | 52.5 ms | 3.8 ms |
| razor | [razor.sample](tests/Fixtures/samples/razor.sample) | 138 | 216 ms | 230 ms | 21.9 ms | 485 ms | 473 ms | 23.9 ms |
| reg | [reg.sample](tests/Fixtures/samples/reg.sample) | 27 | 2.3 ms | 2.1 ms | 0.1 ms | 6.9 ms | 6.4 ms | 0.3 ms |
| regexp | [regexp.sample](tests/Fixtures/samples/regexp.sample) | 30 | 18.7 ms | 17.0 ms | 0.9 ms | 41.7 ms | 39.8 ms | 1.4 ms |
| rel | [rel.sample](tests/Fixtures/samples/rel.sample) | 66 | 9.7 ms | 8.2 ms | 0.9 ms | 16.6 ms | 15.0 ms | 1.5 ms |
| riscv | [riscv.sample](tests/Fixtures/samples/riscv.sample) | 31 | 8.5 ms | 7.4 ms | 0.4 ms | 15.2 ms | 14.1 ms | 0.6 ms |
| rosmsg | [rosmsg.sample](tests/Fixtures/samples/rosmsg.sample) | 67 | 15.7 ms | 14.4 ms | 0.6 ms | 29.5 ms | 28.0 ms | 1.0 ms |
| rst | [rst.sample](tests/Fixtures/samples/rst.sample) | 149 | 47.8 ms | 45.4 ms | 2.0 ms | 76.5 ms | 73.8 ms | 3.7 ms |
| ruby | [ruby.sample](tests/Fixtures/samples/ruby.sample) | 329 | 378 ms | 379 ms | 23.7 ms | 562 ms | 557 ms | 28.5 ms |
| rust | [rust.sample](tests/Fixtures/samples/rust.sample) | 154 | 121 ms | 112 ms | 6.4 ms | 183 ms | 174 ms | 8.5 ms |
| sas | [sas.sample](tests/Fixtures/samples/sas.sample) | 75 | 27.6 ms | 21.8 ms | 3.6 ms | 47.7 ms | 41.4 ms | 4.6 ms |
| sass | [sass.sample](tests/Fixtures/samples/sass.sample) | 104 | 38.5 ms | 34.0 ms | 1.8 ms | 71.6 ms | 67.1 ms | 2.7 ms |
| scala | [scala.sample](tests/Fixtures/samples/scala.sample) | 218 | 196 ms | 180 ms | 10.1 ms | 271 ms | 251 ms | 13.1 ms |
| scheme | [scheme.sample](tests/Fixtures/samples/scheme.sample) | 27 | 6.0 ms | 5.2 ms | 0.3 ms | 17.5 ms | 16.7 ms | 0.5 ms |
| scss | [scss.sample](tests/Fixtures/samples/scss.sample) | 229 | 173 ms | 139 ms | 24.8 ms | 349 ms | 323 ms | 26.3 ms |
| sdbl | [sdbl.sample](tests/Fixtures/samples/sdbl.sample) | 22 | 3.7 ms | 3.0 ms | 0.7 ms | 6.9 ms | 6.1 ms | 0.9 ms |
| shaderlab | [shaderlab.sample](tests/Fixtures/samples/shaderlab.sample) | 61 | 15.1 ms | 13.0 ms | 1.2 ms | 23.8 ms | 21.8 ms | 1.6 ms |
| shellscript | [shellscript.sample](tests/Fixtures/samples/shellscript.sample) | 200 | 145 ms | 132 ms | 9.4 ms | 386 ms | 376 ms | 12.2 ms |
| shellsession | [shellsession.sample](tests/Fixtures/samples/shellsession.sample) | 14 | 3.1 ms | 2.7 ms | 0.1 ms | 7.5 ms | 6.6 ms | 0.6 ms |
| smalltalk | [smalltalk.sample](tests/Fixtures/samples/smalltalk.sample) | 12 | 4.8 ms | 4.5 ms | 0.2 ms | 17.1 ms | 16.8 ms | 0.3 ms |
| solidity | [solidity.sample](tests/Fixtures/samples/solidity.sample) | 173 | 140 ms | 132 ms | 7.3 ms | 181 ms | 173 ms | 9.1 ms |
| soy | [soy.sample](tests/Fixtures/samples/soy.sample) | 89 | 45.3 ms | 41.1 ms | 1.9 ms | 83.2 ms | 79.6 ms | 2.9 ms |
| sparql | [sparql.sample](tests/Fixtures/samples/sparql.sample) | 8 | 1.9 ms | 1.7 ms | 0.1 ms | 4.8 ms | 4.5 ms | 0.2 ms |
| splunk | [splunk.sample](tests/Fixtures/samples/splunk.sample) | 16 | 5.3 ms | 4.6 ms | 0.5 ms | 10.1 ms | 9.2 ms | 0.8 ms |
| sql | [sql.sample](tests/Fixtures/samples/sql.sample) | 135 | 90.1 ms | 66.1 ms | 13.1 ms | 125 ms | 103 ms | 15.4 ms |
| ssh-config | [ssh-config.sample](tests/Fixtures/samples/ssh-config.sample) | 48 | 6.1 ms | 5.4 ms | 0.3 ms | 10.9 ms | 10.2 ms | 0.6 ms |
| stata | [stata.sample](tests/Fixtures/samples/stata.sample) | 143 | 125 ms | 110 ms | 7.9 ms | 169 ms | 155 ms | 9.7 ms |
| stylus | [stylus.sample](tests/Fixtures/samples/stylus.sample) | 133 | 70.8 ms | 63.9 ms | 3.9 ms | 150 ms | 142 ms | 5.1 ms |
| svelte | [svelte.sample](tests/Fixtures/samples/svelte.sample) | 137 | 1278 ms | 1342 ms | 19.7 ms | 1614 ms | 1702 ms | 21.8 ms |
| swift | [swift.sample](tests/Fixtures/samples/swift.sample) | 502 | 593 ms | 541 ms | 35.0 ms | 913 ms | 913 ms | 45.5 ms |
| system-verilog | [system-verilog.sample](tests/Fixtures/samples/system-verilog.sample) | 184 | 110 ms | 102 ms | 11.3 ms | 172 ms | 177 ms | 13.7 ms |
| systemd | [systemd.sample](tests/Fixtures/samples/systemd.sample) | 22 | 2.4 ms | 2.2 ms | 0.1 ms | 5.3 ms | 5.0 ms | 0.2 ms |
| talonscript | [talonscript.sample](tests/Fixtures/samples/talonscript.sample) | 120 | 22.7 ms | 20.8 ms | 1.0 ms | 40.9 ms | 39.2 ms | 1.9 ms |
| tasl | [tasl.sample](tests/Fixtures/samples/tasl.sample) | 26 | 3.6 ms | 3.4 ms | 0.1 ms | 9.3 ms | 9.2 ms | 0.3 ms |
| tcl | [tcl.sample](tests/Fixtures/samples/tcl.sample) | 162 | 23.1 ms | 20.4 ms | 1.4 ms | 44.9 ms | 41.7 ms | 2.8 ms |
| templ | [templ.sample](tests/Fixtures/samples/templ.sample) | 113 | 54.3 ms | 115 ms | 3.5 ms | 184 ms | 286 ms | 5.3 ms |
| terraform | [terraform.sample](tests/Fixtures/samples/terraform.sample) | 162 | 63.6 ms | 59.1 ms | 2.9 ms | 142 ms | 137 ms | 4.4 ms |
| tex | [tex.sample](tests/Fixtures/samples/tex.sample) | 30 | 5.3 ms | 4.6 ms | 0.3 ms | 14.0 ms | 13.2 ms | 0.5 ms |
| toml | [toml.sample](tests/Fixtures/samples/toml.sample) | 26 | 5.6 ms | 5.1 ms | 0.2 ms | 16.9 ms | 16.4 ms | 0.3 ms |
| ts-tags | [ts-tags.sample](tests/Fixtures/samples/ts-tags.sample) | 50 | 26.3 ms | 25.2 ms | 0.9 ms | 50.7 ms | 49.5 ms | 5.9 ms |
| tsv | [tsv.sample](tests/Fixtures/samples/tsv.sample) | 6 | 0.7 ms | 0.7 ms | 0.0 ms | 2.6 ms | 2.5 ms | 0.1 ms |
| tsx | [tsx.sample](tests/Fixtures/samples/tsx.sample) | 518 | 675 ms | 617 ms | 40.2 ms | 1517 ms | 1443 ms | 50.7 ms |
| turtle | [turtle.sample](tests/Fixtures/samples/turtle.sample) | 16 | 3.3 ms | 3.0 ms | 0.1 ms | 8.3 ms | 7.9 ms | 0.3 ms |
| twig | [twig.sample](tests/Fixtures/samples/twig.sample) | 120 | 84.2 ms | 74.5 ms | 5.0 ms | 189 ms | 180 ms | 6.7 ms |
| txt | [txt.sample](tests/Fixtures/samples/txt.sample) | 33 | 0.5 ms | 0.4 ms | 0.1 ms | 1.4 ms | 1.4 ms | 0.4 ms |
| typescript | [typescript.sample](tests/Fixtures/samples/typescript.sample) | 530 | 680 ms | 638 ms | 41.1 ms | 1314 ms | 1309 ms | 127 ms |
| typespec | [typespec.sample](tests/Fixtures/samples/typespec.sample) | 36 | 13.9 ms | 12.6 ms | 0.6 ms | 45.9 ms | 44.5 ms | 0.8 ms |
| typst | [typst.sample](tests/Fixtures/samples/typst.sample) | 28 | 10.7 ms | 11.2 ms | 0.5 ms | 23.8 ms | 25.0 ms | 0.8 ms |
| v | [v.sample](tests/Fixtures/samples/v.sample) | 203 | 101 ms | 90.5 ms | 5.3 ms | 141 ms | 131 ms | 7.3 ms |
| vala | [vala.sample](tests/Fixtures/samples/vala.sample) | 171 | 34.4 ms | 30.0 ms | 1.9 ms | 56.9 ms | 52.3 ms | 3.5 ms |
| vb | [vb.sample](tests/Fixtures/samples/vb.sample) | 39 | 14.3 ms | 12.0 ms | 1.2 ms | 27.5 ms | 24.8 ms | 1.5 ms |
| verilog | [verilog.sample](tests/Fixtures/samples/verilog.sample) | 31 | 5.9 ms | 5.4 ms | 0.3 ms | 12.5 ms | 12.0 ms | 0.5 ms |
| vhdl | [vhdl.sample](tests/Fixtures/samples/vhdl.sample) | 216 | 109 ms | 97.0 ms | 45.6 ms | 160 ms | 146 ms | 51.3 ms |
| viml | [viml.sample](tests/Fixtures/samples/viml.sample) | 126 | 56.5 ms | 46.8 ms | 5.2 ms | 79.7 ms | 70.4 ms | 6.4 ms |
| vue | [vue.sample](tests/Fixtures/samples/vue.sample) | 188 | 194 ms | 154 ms | 10.1 ms | 500 ms | 487 ms | 19.8 ms |
| vue-html | [vue-html.sample](tests/Fixtures/samples/vue-html.sample) | 71 | 67.2 ms | 60.7 ms | 4.3 ms | 129 ms | 122 ms | 5.9 ms |
| vue-vine | [vue-vine.sample](tests/Fixtures/samples/vue-vine.sample) | 253 | 309 ms | 290 ms | 19.8 ms | 848 ms | 824 ms | 23.9 ms |
| vyper | [vyper.sample](tests/Fixtures/samples/vyper.sample) | 192 | 168 ms | 151 ms | 11.1 ms | 241 ms | 223 ms | 13.1 ms |
| wasm | [wasm.sample](tests/Fixtures/samples/wasm.sample) | 175 | 119 ms | 110 ms | 5.7 ms | 152 ms | 143 ms | 8.4 ms |
| wgsl | [wgsl.sample](tests/Fixtures/samples/wgsl.sample) | 19 | 8.5 ms | 8.1 ms | 0.4 ms | 16.6 ms | 16.1 ms | 0.6 ms |
| wikitext | [wikitext.sample](tests/Fixtures/samples/wikitext.sample) | 33 | 42.1 ms | 39.8 ms | 3.0 ms | 56.6 ms | 54.5 ms | 3.9 ms |
| wit | [wit.sample](tests/Fixtures/samples/wit.sample) | 59 | 20.9 ms | 16.9 ms | 0.6 ms | 93.7 ms | 92.6 ms | 1.0 ms |
| wolfram | [wolfram.sample](tests/Fixtures/samples/wolfram.sample) | 176 | 1042 ms | 1038 ms | 63.8 ms | 1192 ms | 1195 ms | 69.4 ms |
| xml | [xml.sample](tests/Fixtures/samples/xml.sample) | 123 | 24.5 ms | 23.1 ms | 1.0 ms | 43.9 ms | 41.3 ms | 2.3 ms |
| xsl | [xsl.sample](tests/Fixtures/samples/xsl.sample) | 66 | 12.0 ms | 10.9 ms | 0.4 ms | 27.7 ms | 26.4 ms | 0.9 ms |
| yaml | [yaml.sample](tests/Fixtures/samples/yaml.sample) | 87 | 33.3 ms | 28.6 ms | 1.8 ms | 78.8 ms | 73.4 ms | 2.5 ms |
| zenscript | [zenscript.sample](tests/Fixtures/samples/zenscript.sample) | 44 | 8.3 ms | 7.5 ms | 0.5 ms | 17.2 ms | 16.2 ms | 0.9 ms |
| zig | [zig.sample](tests/Fixtures/samples/zig.sample) | 26 | 14.5 ms | 13.1 ms | 0.6 ms | 22.3 ms | 20.9 ms | 1.1 ms |
