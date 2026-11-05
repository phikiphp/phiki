# Performance

This report times how long Phiki takes to tokenize and highlight the sample of each bundled grammar with each of its regex engines, Oniguruma and PCRE. It compares tokenizing with [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki, and highlighting with [Shiki](https://shiki.style), using its Oniguruma engine.

Regenerate it with `composer performance` after changing an engine, the tokenizer, the highlighter or the HTML output. It needs Node.js and `npm ci`. Timings depend on the machine, so compare reports generated on the same one.

- **Tokenize** is `Phiki::codeToTokens()`, and a call to `tokenizeLine()` for each line in vscode-textmate.
- **Highlight** is `Phiki::codeToHtml()` with the `github-light` theme, rendered to a string, and `codeToHtml()` in Shiki.
- Every implementation uses Phiki's bundled grammars and theme, so they all work from the same input.
- Each time is the median of 5 runs in PHP and 10 in Node.js. Before timing, each sample is tokenized and highlighted once in PHP and 10 times in Node.js, so V8 has optimized the code. Loading and compiling the grammars is not counted.
- **Compared with JavaScript** is how many times longer Phiki takes than vscode-textmate or Shiki for all 227 samples together. **Typical sample** is the geometric mean of that for each sample, so the largest samples don't outweigh the rest.

Generated with PHP 8.4.27 (OPcache disabled, JIT disabled), PCRE2 10.42, Oniguruma 6.9.9 and Node.js v22.23.4, on AMD EPYC 7763 64-Core Processor.

## Summary

| Task | Implementation | All samples | Compared with JavaScript | Typical sample compared with JavaScript |
| --- | --- | ---: | ---: | ---: |
| Tokenize | vscode-textmate | 1760 ms | – | – |
| Tokenize | Phiki, Oniguruma | 28473 ms | 16.2× | 18.8× |
| Tokenize | Phiki, PCRE | 26537 ms | 15.1× | 17.4× |
| Highlight | Shiki | 2413 ms | – | – |
| Highlight | Phiki, Oniguruma | 50009 ms | 20.7× | 23.3× |
| Highlight | Phiki, PCRE | 48199 ms | 20.0× | 22.6× |

## Grammars

| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| abap | [abap.sample](tests/Fixtures/samples/abap.sample) | 152 | 60.2 ms | 44.7 ms | 11.5 ms | 83.8 ms | 68.0 ms | 13.1 ms |
| actionscript-3 | [actionscript-3.sample](tests/Fixtures/samples/actionscript-3.sample) | 154 | 53.4 ms | 49.7 ms | 3.3 ms | 127 ms | 127 ms | 5.4 ms |
| ada | [ada.sample](tests/Fixtures/samples/ada.sample) | 226 | 122 ms | 112 ms | 5.8 ms | 359 ms | 350 ms | 8.8 ms |
| angular-html | [angular-html.sample](tests/Fixtures/samples/angular-html.sample) | 19 | 14.5 ms | 13.9 ms | 0.5 ms | 40.9 ms | 40.1 ms | 1.3 ms |
| angular-ts | [angular-ts.sample](tests/Fixtures/samples/angular-ts.sample) | 209 | 293 ms | 267 ms | 16.3 ms | 695 ms | 674 ms | 58.6 ms |
| antlers | [antlers.sample](tests/Fixtures/samples/antlers.sample) | 105 | 298 ms | 287 ms | 7.1 ms | 382 ms | 370 ms | 9.9 ms |
| apache | [apache.sample](tests/Fixtures/samples/apache.sample) | 148 | 51.5 ms | 46.0 ms | 4.2 ms | 72.2 ms | 66.0 ms | 5.4 ms |
| apex | [apex.sample](tests/Fixtures/samples/apex.sample) | 213 | 192 ms | 174 ms | 10.0 ms | 250 ms | 233 ms | 13.1 ms |
| apl | [apl.sample](tests/Fixtures/samples/apl.sample) | 164 | 214 ms | 205 ms | 8.5 ms | 302 ms | 308 ms | 10.7 ms |
| applescript | [applescript.sample](tests/Fixtures/samples/applescript.sample) | 126 | 98.3 ms | 91.0 ms | 16.4 ms | 286 ms | 275 ms | 21.3 ms |
| ara | [ara.sample](tests/Fixtures/samples/ara.sample) | 186 | 72.4 ms | 68.5 ms | 4.9 ms | 106 ms | 100 ms | 6.8 ms |
| asciidoc | [asciidoc.sample](tests/Fixtures/samples/asciidoc.sample) | 249 | 199 ms | 186 ms | 9.4 ms | 325 ms | 313 ms | 5.4 ms |
| asm | [asm.sample](tests/Fixtures/samples/asm.sample) | 238 | 381 ms | 354 ms | 27.0 ms | 423 ms | 396 ms | 28.8 ms |
| astro | [astro.sample](tests/Fixtures/samples/astro.sample) | 122 | 880 ms | 867 ms | 14.4 ms | 1244 ms | 1231 ms | 16.5 ms |
| awk | [awk.sample](tests/Fixtures/samples/awk.sample) | 25 | 5.5 ms | 5.3 ms | 0.2 ms | 10.9 ms | 10.8 ms | 0.4 ms |
| ballerina | [ballerina.sample](tests/Fixtures/samples/ballerina.sample) | 227 | 216 ms | 199 ms | 10.8 ms | 466 ms | 453 ms | 13.6 ms |
| bat | [bat.sample](tests/Fixtures/samples/bat.sample) | 82 | 38.4 ms | 34.2 ms | 2.5 ms | 86.4 ms | 82.5 ms | 3.3 ms |
| beancount | [beancount.sample](tests/Fixtures/samples/beancount.sample) | 81 | 25.5 ms | 23.4 ms | 0.9 ms | 67.0 ms | 65.5 ms | 2.2 ms |
| berry | [berry.sample](tests/Fixtures/samples/berry.sample) | 60 | 13.3 ms | 12.5 ms | 0.5 ms | 21.9 ms | 20.7 ms | 1.1 ms |
| bibtex | [bibtex.sample](tests/Fixtures/samples/bibtex.sample) | 29 | 4.1 ms | 3.9 ms | 0.1 ms | 12.0 ms | 11.7 ms | 0.4 ms |
| blade | [blade.sample](tests/Fixtures/samples/blade.sample) | 213 | 340 ms | 317 ms | 16.6 ms | 648 ms | 633 ms | 20.6 ms |
| bsl | [bsl.sample](tests/Fixtures/samples/bsl.sample) | 87 | 44.2 ms | 34.8 ms | 8.9 ms | 64.0 ms | 54.7 ms | 9.8 ms |
| c | [c.sample](tests/Fixtures/samples/c.sample) | 437 | 393 ms | 372 ms | 20.7 ms | 641 ms | 617 ms | 26.8 ms |
| cadence | [cadence.sample](tests/Fixtures/samples/cadence.sample) | 178 | 137 ms | 130 ms | 6.3 ms | 355 ms | 346 ms | 8.5 ms |
| cairo | [cairo.sample](tests/Fixtures/samples/cairo.sample) | 156 | 22.7 ms | 20.8 ms | 1.3 ms | 51.1 ms | 48.9 ms | 2.3 ms |
| clarity | [clarity.sample](tests/Fixtures/samples/clarity.sample) | 53 | 14.0 ms | 12.8 ms | 0.5 ms | 46.2 ms | 45.0 ms | 1.0 ms |
| clojure | [clojure.sample](tests/Fixtures/samples/clojure.sample) | 14 | 10.3 ms | 9.6 ms | 0.5 ms | 33.5 ms | 33.1 ms | 0.8 ms |
| cmake | [cmake.sample](tests/Fixtures/samples/cmake.sample) | 139 | 28.7 ms | 22.5 ms | 3.5 ms | 41.4 ms | 35.1 ms | 4.5 ms |
| cobol | [cobol.sample](tests/Fixtures/samples/cobol.sample) | 228 | 257 ms | 185 ms | 49.1 ms | 305 ms | 237 ms | 51.8 ms |
| codeowners | [codeowners.sample](tests/Fixtures/samples/codeowners.sample) | 8 | 0.7 ms | 0.7 ms | 0.0 ms | 2.4 ms | 2.4 ms | 0.1 ms |
| codeql | [codeql.sample](tests/Fixtures/samples/codeql.sample) | 103 | 99.3 ms | 93.7 ms | 4.5 ms | 179 ms | 175 ms | 5.6 ms |
| coffee | [coffee.sample](tests/Fixtures/samples/coffee.sample) | 151 | 131 ms | 121 ms | 7.4 ms | 231 ms | 220 ms | 9.8 ms |
| common-lisp | [common-lisp.sample](tests/Fixtures/samples/common-lisp.sample) | 29 | 20.7 ms | 14.5 ms | 5.4 ms | 32.9 ms | 26.0 ms | 6.1 ms |
| coq | [coq.sample](tests/Fixtures/samples/coq.sample) | 37 | 10.8 ms | 9.8 ms | 0.6 ms | 18.7 ms | 17.9 ms | 1.1 ms |
| cpp | [cpp.sample](tests/Fixtures/samples/cpp.sample) | 627 | 1303 ms | 1077 ms | 156 ms | 2411 ms | 2121 ms | 173 ms |
| crystal | [crystal.sample](tests/Fixtures/samples/crystal.sample) | 184 | 161 ms | 151 ms | 8.3 ms | 233 ms | 227 ms | 10.2 ms |
| csharp | [csharp.sample](tests/Fixtures/samples/csharp.sample) | 503 | 513 ms | 481 ms | 23.4 ms | 697 ms | 651 ms | 32.4 ms |
| css | [css.sample](tests/Fixtures/samples/css.sample) | 236 | 121 ms | 95.0 ms | 12.0 ms | 301 ms | 275 ms | 15.5 ms |
| csv | [csv.sample](tests/Fixtures/samples/csv.sample) | 5 | 0.8 ms | 0.7 ms | 0.0 ms | 2.8 ms | 2.8 ms | 0.1 ms |
| cue | [cue.sample](tests/Fixtures/samples/cue.sample) | 192 | 109 ms | 102 ms | 5.9 ms | 221 ms | 209 ms | 7.3 ms |
| cypher | [cypher.sample](tests/Fixtures/samples/cypher.sample) | 88 | 43.6 ms | 39.9 ms | 3.0 ms | 74.4 ms | 71.0 ms | 4.2 ms |
| d | [d.sample](tests/Fixtures/samples/d.sample) | 214 | 382 ms | 371 ms | 16.8 ms | 449 ms | 435 ms | 18.9 ms |
| dart | [dart.sample](tests/Fixtures/samples/dart.sample) | 231 | 103 ms | 96.3 ms | 5.5 ms | 149 ms | 142 ms | 9.2 ms |
| dax | [dax.sample](tests/Fixtures/samples/dax.sample) | 20 | 3.7 ms | 3.4 ms | 0.2 ms | 7.7 ms | 7.4 ms | 0.7 ms |
| desktop | [desktop.sample](tests/Fixtures/samples/desktop.sample) | 22 | 3.8 ms | 4.1 ms | 0.5 ms | 7.1 ms | 8.7 ms | 0.7 ms |
| diff | [diff.sample](tests/Fixtures/samples/diff.sample) | 28 | 2.5 ms | 2.2 ms | 0.5 ms | 5.8 ms | 5.6 ms | 0.7 ms |
| djot | [djot.sample](tests/Fixtures/samples/djot.sample) | 126 | 31.3 ms | 29.7 ms | 1.2 ms | 74.9 ms | 75.1 ms | 1.9 ms |
| docker | [docker.sample](tests/Fixtures/samples/docker.sample) | 19 | 1.8 ms | 1.7 ms | 0.2 ms | 4.2 ms | 4.0 ms | 0.2 ms |
| dotenv | [dotenv.sample](tests/Fixtures/samples/dotenv.sample) | 14 | 2.5 ms | 2.4 ms | 0.1 ms | 6.1 ms | 6.0 ms | 0.2 ms |
| dream-maker | [dream-maker.sample](tests/Fixtures/samples/dream-maker.sample) | 124 | 39.5 ms | 36.2 ms | 2.0 ms | 69.1 ms | 68.1 ms | 3.3 ms |
| edge | [edge.sample](tests/Fixtures/samples/edge.sample) | 24 | 25.0 ms | 23.4 ms | 0.9 ms | 55.0 ms | 54.1 ms | 1.2 ms |
| elixir | [elixir.sample](tests/Fixtures/samples/elixir.sample) | 183 | 152 ms | 146 ms | 7.7 ms | 232 ms | 222 ms | 9.9 ms |
| elm | [elm.sample](tests/Fixtures/samples/elm.sample) | 66 | 17.9 ms | 16.9 ms | 0.6 ms | 38.6 ms | 37.7 ms | 1.0 ms |
| emacs-lisp | [emacs-lisp.sample](tests/Fixtures/samples/emacs-lisp.sample) | 156 | 400 ms | 290 ms | 14.0 ms | 659 ms | 545 ms | 16.0 ms |
| erb | [erb.sample](tests/Fixtures/samples/erb.sample) | 76 | 143 ms | 135 ms | 7.2 ms | 267 ms | 263 ms | 8.1 ms |
| erlang | [erlang.sample](tests/Fixtures/samples/erlang.sample) | 201 | 175 ms | 163 ms | 7.9 ms | 463 ms | 452 ms | 10.6 ms |
| fennel | [fennel.sample](tests/Fixtures/samples/fennel.sample) | 12 | 7.9 ms | 7.4 ms | 0.3 ms | 36.3 ms | 36.0 ms | 0.5 ms |
| fish | [fish.sample](tests/Fixtures/samples/fish.sample) | 104 | 33.8 ms | 30.1 ms | 2.0 ms | 65.7 ms | 61.9 ms | 3.0 ms |
| fluent | [fluent.sample](tests/Fixtures/samples/fluent.sample) | 27 | 4.5 ms | 4.0 ms | 0.1 ms | 13.4 ms | 12.9 ms | 0.4 ms |
| fortran-fixed-form | [fortran-fixed-form.sample](tests/Fixtures/samples/fortran-fixed-form.sample) | 34 | 46.5 ms | 42.6 ms | 2.4 ms | 78.8 ms | 74.2 ms | 2.8 ms |
| fortran-free-form | [fortran-free-form.sample](tests/Fixtures/samples/fortran-free-form.sample) | 183 | 707 ms | 697 ms | 20.1 ms | 1063 ms | 1039 ms | 23.6 ms |
| fsharp | [fsharp.sample](tests/Fixtures/samples/fsharp.sample) | 156 | 79.3 ms | 71.7 ms | 4.9 ms | 123 ms | 116 ms | 7.5 ms |
| gdresource | [gdresource.sample](tests/Fixtures/samples/gdresource.sample) | 95 | 29.2 ms | 26.6 ms | 1.1 ms | 45.5 ms | 43.4 ms | 2.2 ms |
| gdscript | [gdscript.sample](tests/Fixtures/samples/gdscript.sample) | 57 | 34.9 ms | 32.0 ms | 2.0 ms | 68.8 ms | 65.2 ms | 2.6 ms |
| gdshader | [gdshader.sample](tests/Fixtures/samples/gdshader.sample) | 97 | 48.3 ms | 45.3 ms | 2.5 ms | 76.5 ms | 73.4 ms | 3.7 ms |
| genie | [genie.sample](tests/Fixtures/samples/genie.sample) | 109 | 25.6 ms | 23.3 ms | 1.1 ms | 42.1 ms | 41.3 ms | 2.1 ms |
| gherkin | [gherkin.sample](tests/Fixtures/samples/gherkin.sample) | 56 | 8.9 ms | 7.3 ms | 0.9 ms | 17.1 ms | 15.9 ms | 1.2 ms |
| git-commit | [git-commit.sample](tests/Fixtures/samples/git-commit.sample) | 64 | 5.0 ms | 4.6 ms | 0.3 ms | 15.6 ms | 15.3 ms | 0.6 ms |
| git-rebase | [git-rebase.sample](tests/Fixtures/samples/git-rebase.sample) | 37 | 7.3 ms | 6.4 ms | 0.1 ms | 17.2 ms | 16.6 ms | 0.6 ms |
| gleam | [gleam.sample](tests/Fixtures/samples/gleam.sample) | 89 | 26.4 ms | 24.8 ms | 1.3 ms | 43.2 ms | 41.3 ms | 2.5 ms |
| glimmer-js | [glimmer-js.sample](tests/Fixtures/samples/glimmer-js.sample) | 109 | 146 ms | 140 ms | 6.2 ms | 309 ms | 305 ms | 7.3 ms |
| glimmer-ts | [glimmer-ts.sample](tests/Fixtures/samples/glimmer-ts.sample) | 119 | 186 ms | 163 ms | 7.2 ms | 391 ms | 384 ms | 8.8 ms |
| glsl | [glsl.sample](tests/Fixtures/samples/glsl.sample) | 118 | 134 ms | 125 ms | 9.3 ms | 191 ms | 183 ms | 11.9 ms |
| gnuplot | [gnuplot.sample](tests/Fixtures/samples/gnuplot.sample) | 112 | 54.1 ms | 48.9 ms | 2.4 ms | 104 ms | 98.7 ms | 4.0 ms |
| go | [go.sample](tests/Fixtures/samples/go.sample) | 613 | 700 ms | 657 ms | 34.2 ms | 825 ms | 797 ms | 43.8 ms |
| graphql | [graphql.sample](tests/Fixtures/samples/graphql.sample) | 168 | 50.0 ms | 45.8 ms | 1.6 ms | 142 ms | 139 ms | 3.3 ms |
| groovy | [groovy.sample](tests/Fixtures/samples/groovy.sample) | 173 | 119 ms | 112 ms | 6.8 ms | 316 ms | 307 ms | 9.0 ms |
| hack | [hack.sample](tests/Fixtures/samples/hack.sample) | 205 | 177 ms | 162 ms | 13.7 ms | 276 ms | 271 ms | 15.9 ms |
| haml | [haml.sample](tests/Fixtures/samples/haml.sample) | 110 | 89.9 ms | 84.1 ms | 4.9 ms | 173 ms | 170 ms | 5.2 ms |
| handlebars | [handlebars.sample](tests/Fixtures/samples/handlebars.sample) | 86 | 62.7 ms | 57.8 ms | 2.4 ms | 134 ms | 126 ms | 3.7 ms |
| haskell | [haskell.sample](tests/Fixtures/samples/haskell.sample) | 154 | 107 ms | 96.8 ms | 12.1 ms | 190 ms | 178 ms | 13.8 ms |
| haxe | [haxe.sample](tests/Fixtures/samples/haxe.sample) | 172 | 177 ms | 163 ms | 7.6 ms | 359 ms | 345 ms | 10.1 ms |
| hcl | [hcl.sample](tests/Fixtures/samples/hcl.sample) | 110 | 52.1 ms | 49.2 ms | 2.0 ms | 122 ms | 119 ms | 3.3 ms |
| hjson | [hjson.sample](tests/Fixtures/samples/hjson.sample) | 92 | 15.2 ms | 22.6 ms | 0.8 ms | 34.8 ms | 90.8 ms | 1.4 ms |
| hlsl | [hlsl.sample](tests/Fixtures/samples/hlsl.sample) | 167 | 52.3 ms | 47.0 ms | 4.9 ms | 75.4 ms | 70.0 ms | 9.2 ms |
| html | [html.sample](tests/Fixtures/samples/html.sample) | 129 | 223 ms | 216 ms | 12.5 ms | 564 ms | 547 ms | 16.5 ms |
| html-derivative | [html-derivative.sample](tests/Fixtures/samples/html-derivative.sample) | 70 | 142 ms | 131 ms | 3.8 ms | 324 ms | 315 ms | 7.6 ms |
| http | [http.sample](tests/Fixtures/samples/http.sample) | 94 | 24.2 ms | 23.0 ms | 0.8 ms | 73.9 ms | 72.7 ms | 1.5 ms |
| hurl | [hurl.sample](tests/Fixtures/samples/hurl.sample) | 170 | 26.6 ms | 25.3 ms | 0.9 ms | 62.4 ms | 60.6 ms | 2.0 ms |
| hxml | [hxml.sample](tests/Fixtures/samples/hxml.sample) | 10 | 4.1 ms | 3.8 ms | 0.1 ms | 8.0 ms | 7.8 ms | 0.2 ms |
| hy | [hy.sample](tests/Fixtures/samples/hy.sample) | 33 | 1.7 ms | 6.0 ms | 0.4 ms | 4.2 ms | 12.1 ms | 0.7 ms |
| imba | [imba.sample](tests/Fixtures/samples/imba.sample) | 164 | 156 ms | 141 ms | 30.0 ms | 249 ms | 236 ms | 33.2 ms |
| ini | [ini.sample](tests/Fixtures/samples/ini.sample) | 13 | 1.5 ms | 1.4 ms | 0.0 ms | 5.0 ms | 4.9 ms | 0.1 ms |
| java | [java.sample](tests/Fixtures/samples/java.sample) | 280 | 236 ms | 222 ms | 14.1 ms | 514 ms | 498 ms | 17.7 ms |
| javascript | [javascript.sample](tests/Fixtures/samples/javascript.sample) | 710 | 909 ms | 832 ms | 48.0 ms | 1631 ms | 1548 ms | 201 ms |
| jinja | [jinja.sample](tests/Fixtures/samples/jinja.sample) | 64 | 29.5 ms | 27.8 ms | 1.2 ms | 58.5 ms | 56.9 ms | 2.2 ms |
| jison | [jison.sample](tests/Fixtures/samples/jison.sample) | 61 | 48.7 ms | 46.9 ms | 1.4 ms | 72.8 ms | 70.2 ms | 2.0 ms |
| json | [json.sample](tests/Fixtures/samples/json.sample) | 51 | 13.5 ms | 12.7 ms | 0.4 ms | 42.3 ms | 41.3 ms | 0.8 ms |
| json5 | [json5.sample](tests/Fixtures/samples/json5.sample) | 42 | 16.1 ms | 15.4 ms | 0.5 ms | 36.5 ms | 35.7 ms | 0.8 ms |
| jsonc | [jsonc.sample](tests/Fixtures/samples/jsonc.sample) | 35 | 12.9 ms | 12.4 ms | 0.3 ms | 40.8 ms | 39.9 ms | 0.7 ms |
| jsonl | [jsonl.sample](tests/Fixtures/samples/jsonl.sample) | 32 | 10.2 ms | 9.9 ms | 0.3 ms | 24.5 ms | 24.1 ms | 0.6 ms |
| jsonnet | [jsonnet.sample](tests/Fixtures/samples/jsonnet.sample) | 34 | 8.4 ms | 7.6 ms | 0.3 ms | 13.7 ms | 13.1 ms | 0.7 ms |
| jssm | [jssm.sample](tests/Fixtures/samples/jssm.sample) | 22 | 19.1 ms | 17.8 ms | 0.7 ms | 26.8 ms | 25.3 ms | 0.9 ms |
| jsx | [jsx.sample](tests/Fixtures/samples/jsx.sample) | 719 | 870 ms | 823 ms | 47.3 ms | 1882 ms | 1821 ms | 59.6 ms |
| julia | [julia.sample](tests/Fixtures/samples/julia.sample) | 157 | 108 ms | 99.4 ms | 5.4 ms | 158 ms | 150 ms | 7.5 ms |
| kdl | [kdl.sample](tests/Fixtures/samples/kdl.sample) | 76 | 16.7 ms | 15.6 ms | 0.6 ms | 29.2 ms | 27.8 ms | 1.2 ms |
| kotlin | [kotlin.sample](tests/Fixtures/samples/kotlin.sample) | 190 | 62.9 ms | 60.8 ms | 3.1 ms | 93.8 ms | 96.4 ms | 5.2 ms |
| kusto | [kusto.sample](tests/Fixtures/samples/kusto.sample) | 72 | 41.6 ms | 37.7 ms | 2.8 ms | 74.1 ms | 70.1 ms | 4.0 ms |
| latex | [latex.sample](tests/Fixtures/samples/latex.sample) | 204 | 154 ms | 163 ms | 30.4 ms | 297 ms | 301 ms | 33.2 ms |
| lean | [lean.sample](tests/Fixtures/samples/lean.sample) | 128 | 19.4 ms | 17.4 ms | 1.3 ms | 29.7 ms | 27.5 ms | 2.1 ms |
| less | [less.sample](tests/Fixtures/samples/less.sample) | 212 | 282 ms | 267 ms | 15.7 ms | 623 ms | 609 ms | 19.8 ms |
| liquid | [liquid.sample](tests/Fixtures/samples/liquid.sample) | 121 | 91.7 ms | 84.7 ms | 2.9 ms | 188 ms | 187 ms | 4.2 ms |
| llvm | [llvm.sample](tests/Fixtures/samples/llvm.sample) | 26 | 11.9 ms | 10.0 ms | 0.8 ms | 17.8 ms | 16.1 ms | 1.2 ms |
| log | [log.sample](tests/Fixtures/samples/log.sample) | 47 | 22.6 ms | 16.4 ms | 2.1 ms | 33.3 ms | 25.9 ms | 2.9 ms |
| logo | [logo.sample](tests/Fixtures/samples/logo.sample) | 22 | 3.1 ms | 2.7 ms | 0.1 ms | 6.9 ms | 6.5 ms | 0.3 ms |
| lua | [lua.sample](tests/Fixtures/samples/lua.sample) | 204 | 100 ms | 95.2 ms | 4.2 ms | 162 ms | 151 ms | 6.5 ms |
| luau | [luau.sample](tests/Fixtures/samples/luau.sample) | 97 | 56.6 ms | 53.2 ms | 2.4 ms | 94.2 ms | 89.0 ms | 3.6 ms |
| make | [make.sample](tests/Fixtures/samples/make.sample) | 117 | 34.0 ms | 31.8 ms | 1.1 ms | 80.1 ms | 78.1 ms | 2.2 ms |
| maml | [maml.sample](tests/Fixtures/samples/maml.sample) | 29 | 6.8 ms | 6.5 ms | 0.2 ms | 27.5 ms | 27.2 ms | 0.3 ms |
| markdown | [markdown.sample](tests/Fixtures/samples/markdown.sample) | 449 | 340 ms | 322 ms | 17.1 ms | 695 ms | 700 ms | 10.6 ms |
| marko | [marko.sample](tests/Fixtures/samples/marko.sample) | 125 | 219 ms | 195 ms | 12.4 ms | 360 ms | 334 ms | 14.3 ms |
| matlab | [matlab.sample](tests/Fixtures/samples/matlab.sample) | 115 | 41.0 ms | 37.4 ms | 1.7 ms | 96.3 ms | 92.1 ms | 2.8 ms |
| mdc | [mdc.sample](tests/Fixtures/samples/mdc.sample) | 34 | 18.6 ms | 17.5 ms | 23.4 ms | 40.9 ms | 41.7 ms | 24.9 ms |
| mdx | [mdx.sample](tests/Fixtures/samples/mdx.sample) | 135 | 139 ms | 147 ms | 4.6 ms | 277 ms | 289 ms | 5.7 ms |
| mermaid | [mermaid.sample](tests/Fixtures/samples/mermaid.sample) | 224 | 40.5 ms | 33.9 ms | 3.1 ms | 66.7 ms | 61.7 ms | 5.2 ms |
| mipsasm | [mipsasm.sample](tests/Fixtures/samples/mipsasm.sample) | 25 | 4.5 ms | 4.1 ms | 0.2 ms | 9.4 ms | 9.1 ms | 0.3 ms |
| mojo | [mojo.sample](tests/Fixtures/samples/mojo.sample) | 209 | 195 ms | 183 ms | 11.3 ms | 348 ms | 336 ms | 13.5 ms |
| move | [move.sample](tests/Fixtures/samples/move.sample) | 151 | 60.3 ms | 56.7 ms | 2.5 ms | 184 ms | 179 ms | 4.2 ms |
| narrat | [narrat.sample](tests/Fixtures/samples/narrat.sample) | 92 | 21.4 ms | 19.5 ms | 1.5 ms | 36.1 ms | 34.3 ms | 2.2 ms |
| neon | [neon.sample](tests/Fixtures/samples/neon.sample) | 97 | 19.5 ms | 18.0 ms | 0.7 ms | 41.3 ms | 39.5 ms | 1.3 ms |
| nextflow | [nextflow.sample](tests/Fixtures/samples/nextflow.sample) | 138 | 7.0 ms | 6.7 ms | 0.4 ms | 15.5 ms | 14.9 ms | 1.2 ms |
| nginx | [nginx.sample](tests/Fixtures/samples/nginx.sample) | 213 | 104 ms | 99.0 ms | 3.1 ms | 169 ms | 167 ms | 4.6 ms |
| nim | [nim.sample](tests/Fixtures/samples/nim.sample) | 177 | 102 ms | 91.5 ms | 7.9 ms | 150 ms | 142 ms | 10.1 ms |
| nix | [nix.sample](tests/Fixtures/samples/nix.sample) | 45 | 23.1 ms | 21.2 ms | 0.7 ms | 33.5 ms | 31.7 ms | 1.0 ms |
| nushell | [nushell.sample](tests/Fixtures/samples/nushell.sample) | 68 | 51.6 ms | 49.1 ms | 2.3 ms | 142 ms | 138 ms | 3.2 ms |
| objective-c | [objective-c.sample](tests/Fixtures/samples/objective-c.sample) | 177 | 199 ms | 187 ms | 12.7 ms | 412 ms | 398 ms | 15.1 ms |
| objective-cpp | [objective-cpp.sample](tests/Fixtures/samples/objective-cpp.sample) | 229 | 335 ms | 307 ms | 24.6 ms | 656 ms | 630 ms | 28.0 ms |
| ocaml | [ocaml.sample](tests/Fixtures/samples/ocaml.sample) | 140 | 136 ms | 125 ms | 8.4 ms | 178 ms | 165 ms | 10.5 ms |
| pascal | [pascal.sample](tests/Fixtures/samples/pascal.sample) | 110 | 17.7 ms | 14.3 ms | 2.2 ms | 28.0 ms | 24.5 ms | 3.0 ms |
| perl | [perl.sample](tests/Fixtures/samples/perl.sample) | 170 | 121 ms | 112 ms | 5.9 ms | 226 ms | 217 ms | 8.0 ms |
| php | [php.sample](tests/Fixtures/samples/php.sample) | 465 | 651 ms | 584 ms | 44.9 ms | 1236 ms | 1176 ms | 54.5 ms |
| pkl | [pkl.sample](tests/Fixtures/samples/pkl.sample) | 138 | 57.4 ms | 53.3 ms | 2.5 ms | 86.2 ms | 81.1 ms | 3.8 ms |
| plsql | [plsql.sample](tests/Fixtures/samples/plsql.sample) | 121 | 53.2 ms | 44.7 ms | 8.2 ms | 78.9 ms | 68.5 ms | 9.7 ms |
| polar | [polar.sample](tests/Fixtures/samples/polar.sample) | 150 | 38.0 ms | 34.7 ms | 1.5 ms | 70.5 ms | 68.1 ms | 2.9 ms |
| postcss | [postcss.sample](tests/Fixtures/samples/postcss.sample) | 141 | 39.7 ms | 36.5 ms | 1.8 ms | 63.7 ms | 61.1 ms | 3.6 ms |
| powerquery | [powerquery.sample](tests/Fixtures/samples/powerquery.sample) | 23 | 11.8 ms | 10.8 ms | 0.6 ms | 20.6 ms | 19.8 ms | 0.8 ms |
| powershell | [powershell.sample](tests/Fixtures/samples/powershell.sample) | 146 | 89.6 ms | 83.1 ms | 5.8 ms | 219 ms | 212 ms | 7.5 ms |
| prisma | [prisma.sample](tests/Fixtures/samples/prisma.sample) | 42 | 6.9 ms | 6.5 ms | 0.2 ms | 22.4 ms | 22.0 ms | 0.5 ms |
| prolog | [prolog.sample](tests/Fixtures/samples/prolog.sample) | 180 | 96.7 ms | 88.5 ms | 4.0 ms | 148 ms | 143 ms | 6.0 ms |
| proto | [proto.sample](tests/Fixtures/samples/proto.sample) | 45 | 7.3 ms | 6.8 ms | 0.2 ms | 16.4 ms | 16.2 ms | 0.6 ms |
| pug | [pug.sample](tests/Fixtures/samples/pug.sample) | 87 | 91.0 ms | 90.4 ms | 58.8 ms | 172 ms | 164 ms | 61.7 ms |
| puppet | [puppet.sample](tests/Fixtures/samples/puppet.sample) | 138 | 44.4 ms | 40.7 ms | 1.9 ms | 97.1 ms | 93.2 ms | 3.2 ms |
| purescript | [purescript.sample](tests/Fixtures/samples/purescript.sample) | 82 | 44.9 ms | 41.2 ms | 2.0 ms | 77.1 ms | 76.2 ms | 3.0 ms |
| python | [python.sample](tests/Fixtures/samples/python.sample) | 348 | 335 ms | 332 ms | 21.7 ms | 607 ms | 576 ms | 28.2 ms |
| qml | [qml.sample](tests/Fixtures/samples/qml.sample) | 27 | 34.9 ms | 32.6 ms | 1.4 ms | 53.3 ms | 49.9 ms | 1.6 ms |
| qmldir | [qmldir.sample](tests/Fixtures/samples/qmldir.sample) | 8 | 1.0 ms | 0.9 ms | 0.0 ms | 3.3 ms | 3.3 ms | 0.1 ms |
| qss | [qss.sample](tests/Fixtures/samples/qss.sample) | 37 | 7.2 ms | 6.5 ms | 0.3 ms | 13.6 ms | 12.9 ms | 0.7 ms |
| r | [r.sample](tests/Fixtures/samples/r.sample) | 106 | 129 ms | 109 ms | 9.9 ms | 196 ms | 173 ms | 11.7 ms |
| racket | [racket.sample](tests/Fixtures/samples/racket.sample) | 127 | 261 ms | 211 ms | 10.9 ms | 330 ms | 281 ms | 12.6 ms |
| raku | [raku.sample](tests/Fixtures/samples/raku.sample) | 134 | 41.7 ms | 36.0 ms | 2.8 ms | 63.7 ms | 58.5 ms | 3.8 ms |
| razor | [razor.sample](tests/Fixtures/samples/razor.sample) | 138 | 237 ms | 252 ms | 20.7 ms | 519 ms | 518 ms | 24.2 ms |
| reg | [reg.sample](tests/Fixtures/samples/reg.sample) | 27 | 2.7 ms | 2.5 ms | 0.1 ms | 7.8 ms | 7.3 ms | 0.3 ms |
| regexp | [regexp.sample](tests/Fixtures/samples/regexp.sample) | 30 | 21.7 ms | 20.7 ms | 0.9 ms | 47.0 ms | 45.6 ms | 1.5 ms |
| rel | [rel.sample](tests/Fixtures/samples/rel.sample) | 66 | 11.7 ms | 10.1 ms | 0.9 ms | 19.1 ms | 17.4 ms | 1.6 ms |
| riscv | [riscv.sample](tests/Fixtures/samples/riscv.sample) | 31 | 9.8 ms | 8.8 ms | 0.4 ms | 17.0 ms | 16.1 ms | 0.6 ms |
| rosmsg | [rosmsg.sample](tests/Fixtures/samples/rosmsg.sample) | 67 | 19.0 ms | 17.7 ms | 0.6 ms | 33.9 ms | 33.1 ms | 1.1 ms |
| rst | [rst.sample](tests/Fixtures/samples/rst.sample) | 149 | 55.2 ms | 51.6 ms | 2.0 ms | 85.0 ms | 84.6 ms | 2.2 ms |
| ruby | [ruby.sample](tests/Fixtures/samples/ruby.sample) | 329 | 421 ms | 415 ms | 23.7 ms | 629 ms | 600 ms | 27.9 ms |
| rust | [rust.sample](tests/Fixtures/samples/rust.sample) | 154 | 135 ms | 128 ms | 6.4 ms | 203 ms | 198 ms | 8.5 ms |
| sas | [sas.sample](tests/Fixtures/samples/sas.sample) | 75 | 31.4 ms | 25.5 ms | 3.7 ms | 53.5 ms | 47.5 ms | 4.8 ms |
| sass | [sass.sample](tests/Fixtures/samples/sass.sample) | 104 | 43.8 ms | 40.2 ms | 1.9 ms | 81.8 ms | 77.2 ms | 2.9 ms |
| scala | [scala.sample](tests/Fixtures/samples/scala.sample) | 218 | 221 ms | 204 ms | 9.7 ms | 305 ms | 288 ms | 13.7 ms |
| scheme | [scheme.sample](tests/Fixtures/samples/scheme.sample) | 27 | 7.1 ms | 6.4 ms | 0.3 ms | 19.8 ms | 19.1 ms | 0.5 ms |
| scss | [scss.sample](tests/Fixtures/samples/scss.sample) | 229 | 191 ms | 154 ms | 23.6 ms | 388 ms | 351 ms | 25.5 ms |
| sdbl | [sdbl.sample](tests/Fixtures/samples/sdbl.sample) | 22 | 4.3 ms | 3.5 ms | 0.7 ms | 7.7 ms | 6.9 ms | 0.9 ms |
| shaderlab | [shaderlab.sample](tests/Fixtures/samples/shaderlab.sample) | 61 | 16.5 ms | 14.6 ms | 1.2 ms | 25.7 ms | 23.9 ms | 1.6 ms |
| shellscript | [shellscript.sample](tests/Fixtures/samples/shellscript.sample) | 200 | 164 ms | 151 ms | 9.5 ms | 427 ms | 413 ms | 11.8 ms |
| shellsession | [shellsession.sample](tests/Fixtures/samples/shellsession.sample) | 14 | 3.5 ms | 3.0 ms | 0.1 ms | 8.2 ms | 7.4 ms | 0.7 ms |
| smalltalk | [smalltalk.sample](tests/Fixtures/samples/smalltalk.sample) | 12 | 5.4 ms | 5.0 ms | 0.2 ms | 18.6 ms | 18.1 ms | 0.4 ms |
| solidity | [solidity.sample](tests/Fixtures/samples/solidity.sample) | 173 | 150 ms | 140 ms | 7.1 ms | 195 ms | 184 ms | 9.0 ms |
| soy | [soy.sample](tests/Fixtures/samples/soy.sample) | 89 | 50.0 ms | 45.5 ms | 2.0 ms | 89.6 ms | 87.6 ms | 3.0 ms |
| sparql | [sparql.sample](tests/Fixtures/samples/sparql.sample) | 8 | 2.2 ms | 2.0 ms | 0.1 ms | 5.3 ms | 5.2 ms | 0.2 ms |
| splunk | [splunk.sample](tests/Fixtures/samples/splunk.sample) | 16 | 6.1 ms | 5.4 ms | 0.5 ms | 11.3 ms | 10.5 ms | 0.9 ms |
| sql | [sql.sample](tests/Fixtures/samples/sql.sample) | 135 | 94.7 ms | 72.1 ms | 13.7 ms | 134 ms | 113 ms | 16.3 ms |
| ssh-config | [ssh-config.sample](tests/Fixtures/samples/ssh-config.sample) | 48 | 7.2 ms | 6.4 ms | 0.3 ms | 12.3 ms | 11.7 ms | 0.6 ms |
| stata | [stata.sample](tests/Fixtures/samples/stata.sample) | 143 | 135 ms | 120 ms | 7.7 ms | 183 ms | 169 ms | 9.8 ms |
| stylus | [stylus.sample](tests/Fixtures/samples/stylus.sample) | 133 | 79.2 ms | 71.5 ms | 3.9 ms | 164 ms | 158 ms | 5.2 ms |
| svelte | [svelte.sample](tests/Fixtures/samples/svelte.sample) | 137 | 1403 ms | 1513 ms | 19.5 ms | 1791 ms | 1897 ms | 20.3 ms |
| swift | [swift.sample](tests/Fixtures/samples/swift.sample) | 502 | 676 ms | 611 ms | 36.0 ms | 1013 ms | 1031 ms | 43.3 ms |
| system-verilog | [system-verilog.sample](tests/Fixtures/samples/system-verilog.sample) | 184 | 122 ms | 117 ms | 11.2 ms | 193 ms | 200 ms | 13.5 ms |
| systemd | [systemd.sample](tests/Fixtures/samples/systemd.sample) | 22 | 2.8 ms | 2.6 ms | 0.1 ms | 6.0 ms | 5.8 ms | 0.2 ms |
| talonscript | [talonscript.sample](tests/Fixtures/samples/talonscript.sample) | 120 | 28.0 ms | 26.2 ms | 1.0 ms | 48.4 ms | 47.3 ms | 2.0 ms |
| tasl | [tasl.sample](tests/Fixtures/samples/tasl.sample) | 26 | 4.4 ms | 4.1 ms | 0.1 ms | 10.7 ms | 10.5 ms | 0.3 ms |
| tcl | [tcl.sample](tests/Fixtures/samples/tcl.sample) | 162 | 27.5 ms | 25.0 ms | 1.4 ms | 50.6 ms | 48.5 ms | 3.0 ms |
| templ | [templ.sample](tests/Fixtures/samples/templ.sample) | 113 | 59.5 ms | 123 ms | 3.4 ms | 203 ms | 318 ms | 5.7 ms |
| terraform | [terraform.sample](tests/Fixtures/samples/terraform.sample) | 162 | 73.4 ms | 69.1 ms | 2.9 ms | 159 ms | 156 ms | 4.6 ms |
| tex | [tex.sample](tests/Fixtures/samples/tex.sample) | 30 | 6.3 ms | 5.7 ms | 0.3 ms | 15.8 ms | 15.1 ms | 0.5 ms |
| toml | [toml.sample](tests/Fixtures/samples/toml.sample) | 26 | 6.8 ms | 6.4 ms | 0.2 ms | 19.2 ms | 19.2 ms | 0.4 ms |
| ts-tags | [ts-tags.sample](tests/Fixtures/samples/ts-tags.sample) | 50 | 29.5 ms | 28.9 ms | 0.9 ms | 56.7 ms | 56.8 ms | 5.6 ms |
| tsv | [tsv.sample](tests/Fixtures/samples/tsv.sample) | 6 | 0.9 ms | 0.8 ms | 0.0 ms | 3.0 ms | 2.9 ms | 0.1 ms |
| tsx | [tsx.sample](tests/Fixtures/samples/tsx.sample) | 518 | 726 ms | 681 ms | 40.8 ms | 1631 ms | 1569 ms | 49.9 ms |
| turtle | [turtle.sample](tests/Fixtures/samples/turtle.sample) | 16 | 3.9 ms | 3.6 ms | 0.2 ms | 9.4 ms | 9.1 ms | 0.4 ms |
| twig | [twig.sample](tests/Fixtures/samples/twig.sample) | 120 | 95.6 ms | 87.0 ms | 5.0 ms | 213 ms | 197 ms | 7.2 ms |
| txt | [txt.sample](tests/Fixtures/samples/txt.sample) | 33 | 0.6 ms | 0.6 ms | 0.1 ms | 1.7 ms | 1.6 ms | 0.3 ms |
| typescript | [typescript.sample](tests/Fixtures/samples/typescript.sample) | 530 | 756 ms | 701 ms | 40.0 ms | 1461 ms | 1438 ms | 121 ms |
| typespec | [typespec.sample](tests/Fixtures/samples/typespec.sample) | 36 | 16.3 ms | 15.1 ms | 0.5 ms | 51.8 ms | 50.0 ms | 0.9 ms |
| typst | [typst.sample](tests/Fixtures/samples/typst.sample) | 28 | 12.0 ms | 13.2 ms | 0.5 ms | 25.9 ms | 28.4 ms | 0.8 ms |
| v | [v.sample](tests/Fixtures/samples/v.sample) | 203 | 114 ms | 103 ms | 5.3 ms | 160 ms | 150 ms | 7.2 ms |
| vala | [vala.sample](tests/Fixtures/samples/vala.sample) | 171 | 40.4 ms | 36.4 ms | 1.9 ms | 64.8 ms | 60.1 ms | 3.6 ms |
| vb | [vb.sample](tests/Fixtures/samples/vb.sample) | 39 | 16.3 ms | 14.6 ms | 1.2 ms | 30.8 ms | 28.6 ms | 1.5 ms |
| verilog | [verilog.sample](tests/Fixtures/samples/verilog.sample) | 31 | 6.8 ms | 6.4 ms | 0.3 ms | 14.0 ms | 13.5 ms | 0.5 ms |
| vhdl | [vhdl.sample](tests/Fixtures/samples/vhdl.sample) | 216 | 124 ms | 111 ms | 43.3 ms | 181 ms | 166 ms | 52.5 ms |
| viml | [viml.sample](tests/Fixtures/samples/viml.sample) | 126 | 63.2 ms | 53.8 ms | 5.0 ms | 89.1 ms | 80.5 ms | 6.3 ms |
| vue | [vue.sample](tests/Fixtures/samples/vue.sample) | 188 | 212 ms | 171 ms | 10.0 ms | 548 ms | 532 ms | 19.9 ms |
| vue-html | [vue-html.sample](tests/Fixtures/samples/vue-html.sample) | 71 | 76.3 ms | 69.0 ms | 4.2 ms | 145 ms | 139 ms | 5.9 ms |
| vue-vine | [vue-vine.sample](tests/Fixtures/samples/vue-vine.sample) | 253 | 346 ms | 325 ms | 19.8 ms | 938 ms | 891 ms | 22.9 ms |
| vyper | [vyper.sample](tests/Fixtures/samples/vyper.sample) | 192 | 189 ms | 174 ms | 10.9 ms | 266 ms | 252 ms | 12.7 ms |
| wasm | [wasm.sample](tests/Fixtures/samples/wasm.sample) | 175 | 132 ms | 125 ms | 5.6 ms | 167 ms | 160 ms | 8.9 ms |
| wgsl | [wgsl.sample](tests/Fixtures/samples/wgsl.sample) | 19 | 9.9 ms | 9.3 ms | 0.8 ms | 18.7 ms | 18.2 ms | 0.6 ms |
| wikitext | [wikitext.sample](tests/Fixtures/samples/wikitext.sample) | 33 | 46.7 ms | 44.2 ms | 3.1 ms | 62.5 ms | 60.3 ms | 3.8 ms |
| wit | [wit.sample](tests/Fixtures/samples/wit.sample) | 59 | 21.3 ms | 20.3 ms | 1.0 ms | 105 ms | 104 ms | 1.1 ms |
| wolfram | [wolfram.sample](tests/Fixtures/samples/wolfram.sample) | 176 | 1129 ms | 1078 ms | 65.4 ms | 1281 ms | 1232 ms | 68.7 ms |
| xml | [xml.sample](tests/Fixtures/samples/xml.sample) | 123 | 30.6 ms | 28.3 ms | 1.0 ms | 50.3 ms | 47.2 ms | 2.4 ms |
| xsl | [xsl.sample](tests/Fixtures/samples/xsl.sample) | 66 | 13.8 ms | 13.0 ms | 0.4 ms | 30.3 ms | 29.7 ms | 1.0 ms |
| yaml | [yaml.sample](tests/Fixtures/samples/yaml.sample) | 87 | 37.3 ms | 33.7 ms | 1.8 ms | 87.3 ms | 83.0 ms | 2.6 ms |
| zenscript | [zenscript.sample](tests/Fixtures/samples/zenscript.sample) | 44 | 9.7 ms | 9.0 ms | 0.5 ms | 19.0 ms | 18.1 ms | 1.0 ms |
| zig | [zig.sample](tests/Fixtures/samples/zig.sample) | 26 | 15.4 ms | 14.8 ms | 0.7 ms | 23.8 ms | 23.0 ms | 1.1 ms |
