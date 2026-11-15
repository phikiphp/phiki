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
| Tokenize | vscode-textmate | 1387 ms | – | – |
| Tokenize | Phiki, Oniguruma | 19831 ms | 14.3× | 16.3× |
| Tokenize | Phiki, PCRE | 18266 ms | 13.2× | 15.0× |
| Highlight | Shiki | 1864 ms | – | – |
| Highlight | Phiki, Oniguruma | 34937 ms | 18.7× | 21.5× |
| Highlight | Phiki, PCRE | 33551 ms | 18.0× | 20.7× |

## Grammars

| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| abap | [abap.sample](tests/Fixtures/samples/abap.sample) | 152 | 43.1 ms | 29.3 ms | 9.1 ms | 60.0 ms | 45.8 ms | 9.9 ms |
| actionscript-3 | [actionscript-3.sample](tests/Fixtures/samples/actionscript-3.sample) | 154 | 35.7 ms | 32.8 ms | 2.5 ms | 87.8 ms | 87.9 ms | 4.1 ms |
| ada | [ada.sample](tests/Fixtures/samples/ada.sample) | 226 | 81.1 ms | 76.0 ms | 4.4 ms | 247 ms | 240 ms | 6.7 ms |
| angular-html | [angular-html.sample](tests/Fixtures/samples/angular-html.sample) | 19 | 9.9 ms | 9.2 ms | 0.3 ms | 28.7 ms | 27.9 ms | 0.9 ms |
| angular-ts | [angular-ts.sample](tests/Fixtures/samples/angular-ts.sample) | 209 | 203 ms | 191 ms | 13.7 ms | 489 ms | 474 ms | 43.3 ms |
| antlers | [antlers.sample](tests/Fixtures/samples/antlers.sample) | 105 | 200 ms | 194 ms | 5.5 ms | 257 ms | 252 ms | 7.0 ms |
| apache | [apache.sample](tests/Fixtures/samples/apache.sample) | 148 | 35.7 ms | 31.3 ms | 3.2 ms | 49.3 ms | 45.2 ms | 4.0 ms |
| apex | [apex.sample](tests/Fixtures/samples/apex.sample) | 213 | 133 ms | 116 ms | 7.7 ms | 171 ms | 160 ms | 10.0 ms |
| apl | [apl.sample](tests/Fixtures/samples/apl.sample) | 164 | 153 ms | 147 ms | 6.5 ms | 213 ms | 204 ms | 8.5 ms |
| applescript | [applescript.sample](tests/Fixtures/samples/applescript.sample) | 126 | 66.4 ms | 59.5 ms | 13.0 ms | 193 ms | 186 ms | 14.6 ms |
| ara | [ara.sample](tests/Fixtures/samples/ara.sample) | 186 | 47.8 ms | 43.5 ms | 3.4 ms | 68.7 ms | 64.7 ms | 5.1 ms |
| asciidoc | [asciidoc.sample](tests/Fixtures/samples/asciidoc.sample) | 249 | 136 ms | 130 ms | 7.6 ms | 222 ms | 216 ms | 4.1 ms |
| asm | [asm.sample](tests/Fixtures/samples/asm.sample) | 238 | 269 ms | 253 ms | 19.6 ms | 303 ms | 283 ms | 21.2 ms |
| astro | [astro.sample](tests/Fixtures/samples/astro.sample) | 122 | 604 ms | 588 ms | 11.8 ms | 862 ms | 852 ms | 13.1 ms |
| awk | [awk.sample](tests/Fixtures/samples/awk.sample) | 25 | 3.7 ms | 3.5 ms | 0.2 ms | 7.5 ms | 7.4 ms | 0.3 ms |
| ballerina | [ballerina.sample](tests/Fixtures/samples/ballerina.sample) | 227 | 148 ms | 142 ms | 8.4 ms | 329 ms | 310 ms | 10.2 ms |
| bat | [bat.sample](tests/Fixtures/samples/bat.sample) | 82 | 25.2 ms | 21.8 ms | 1.9 ms | 58.5 ms | 55.0 ms | 2.4 ms |
| beancount | [beancount.sample](tests/Fixtures/samples/beancount.sample) | 81 | 16.0 ms | 14.5 ms | 0.7 ms | 44.3 ms | 43.3 ms | 1.6 ms |
| berry | [berry.sample](tests/Fixtures/samples/berry.sample) | 60 | 8.5 ms | 7.7 ms | 0.4 ms | 14.3 ms | 13.6 ms | 0.8 ms |
| bibtex | [bibtex.sample](tests/Fixtures/samples/bibtex.sample) | 29 | 2.6 ms | 2.4 ms | 0.1 ms | 8.0 ms | 7.9 ms | 0.3 ms |
| blade | [blade.sample](tests/Fixtures/samples/blade.sample) | 213 | 236 ms | 215 ms | 12.3 ms | 449 ms | 427 ms | 14.9 ms |
| bsl | [bsl.sample](tests/Fixtures/samples/bsl.sample) | 87 | 31.1 ms | 23.1 ms | 6.7 ms | 44.8 ms | 36.9 ms | 7.4 ms |
| c | [c.sample](tests/Fixtures/samples/c.sample) | 437 | 281 ms | 253 ms | 16.9 ms | 445 ms | 420 ms | 21.1 ms |
| cadence | [cadence.sample](tests/Fixtures/samples/cadence.sample) | 178 | 93.1 ms | 87.5 ms | 4.7 ms | 244 ms | 247 ms | 6.4 ms |
| cairo | [cairo.sample](tests/Fixtures/samples/cairo.sample) | 156 | 15.6 ms | 13.7 ms | 1.0 ms | 34.8 ms | 33.2 ms | 1.7 ms |
| clarity | [clarity.sample](tests/Fixtures/samples/clarity.sample) | 53 | 9.1 ms | 8.3 ms | 0.4 ms | 31.9 ms | 30.6 ms | 0.7 ms |
| clojure | [clojure.sample](tests/Fixtures/samples/clojure.sample) | 14 | 6.9 ms | 6.4 ms | 0.4 ms | 23.2 ms | 22.7 ms | 0.6 ms |
| cmake | [cmake.sample](tests/Fixtures/samples/cmake.sample) | 139 | 19.6 ms | 14.6 ms | 2.7 ms | 27.9 ms | 23.2 ms | 3.4 ms |
| cobol | [cobol.sample](tests/Fixtures/samples/cobol.sample) | 228 | 189 ms | 131 ms | 37.0 ms | 221 ms | 164 ms | 37.9 ms |
| codeowners | [codeowners.sample](tests/Fixtures/samples/codeowners.sample) | 8 | 0.4 ms | 0.4 ms | 0.0 ms | 1.6 ms | 1.6 ms | 0.1 ms |
| codeql | [codeql.sample](tests/Fixtures/samples/codeql.sample) | 103 | 67.4 ms | 62.9 ms | 3.5 ms | 122 ms | 119 ms | 4.2 ms |
| coffee | [coffee.sample](tests/Fixtures/samples/coffee.sample) | 151 | 90.8 ms | 81.5 ms | 5.8 ms | 161 ms | 150 ms | 7.6 ms |
| common-lisp | [common-lisp.sample](tests/Fixtures/samples/common-lisp.sample) | 29 | 15.4 ms | 9.5 ms | 4.1 ms | 23.3 ms | 17.5 ms | 4.4 ms |
| coq | [coq.sample](tests/Fixtures/samples/coq.sample) | 37 | 7.2 ms | 6.3 ms | 0.4 ms | 12.7 ms | 11.8 ms | 0.8 ms |
| cpp | [cpp.sample](tests/Fixtures/samples/cpp.sample) | 627 | 948 ms | 731 ms | 123 ms | 1718 ms | 1484 ms | 133 ms |
| crystal | [crystal.sample](tests/Fixtures/samples/crystal.sample) | 184 | 111 ms | 105 ms | 6.5 ms | 162 ms | 154 ms | 7.8 ms |
| csharp | [csharp.sample](tests/Fixtures/samples/csharp.sample) | 503 | 352 ms | 318 ms | 19.4 ms | 481 ms | 434 ms | 25.2 ms |
| css | [css.sample](tests/Fixtures/samples/css.sample) | 236 | 80.9 ms | 60.8 ms | 9.7 ms | 206 ms | 185 ms | 12.4 ms |
| csv | [csv.sample](tests/Fixtures/samples/csv.sample) | 5 | 0.5 ms | 0.5 ms | 0.0 ms | 1.9 ms | 1.9 ms | 0.1 ms |
| cue | [cue.sample](tests/Fixtures/samples/cue.sample) | 192 | 71.6 ms | 67.7 ms | 4.6 ms | 150 ms | 150 ms | 5.6 ms |
| cypher | [cypher.sample](tests/Fixtures/samples/cypher.sample) | 88 | 29.1 ms | 26.0 ms | 2.3 ms | 50.5 ms | 47.3 ms | 3.2 ms |
| d | [d.sample](tests/Fixtures/samples/d.sample) | 214 | 268 ms | 259 ms | 12.9 ms | 312 ms | 317 ms | 15.1 ms |
| dart | [dart.sample](tests/Fixtures/samples/dart.sample) | 231 | 72.1 ms | 66.2 ms | 4.3 ms | 104 ms | 97.7 ms | 6.3 ms |
| dax | [dax.sample](tests/Fixtures/samples/dax.sample) | 20 | 2.4 ms | 2.1 ms | 0.1 ms | 5.2 ms | 4.9 ms | 0.2 ms |
| desktop | [desktop.sample](tests/Fixtures/samples/desktop.sample) | 22 | 2.5 ms | 2.7 ms | 0.4 ms | 4.6 ms | 5.8 ms | 0.5 ms |
| diff | [diff.sample](tests/Fixtures/samples/diff.sample) | 28 | 1.6 ms | 1.5 ms | 0.4 ms | 3.9 ms | 3.7 ms | 0.5 ms |
| djot | [djot.sample](tests/Fixtures/samples/djot.sample) | 126 | 21.8 ms | 20.1 ms | 1.0 ms | 53.2 ms | 51.5 ms | 1.5 ms |
| docker | [docker.sample](tests/Fixtures/samples/docker.sample) | 19 | 1.1 ms | 1.0 ms | 0.1 ms | 2.7 ms | 2.7 ms | 0.2 ms |
| dotenv | [dotenv.sample](tests/Fixtures/samples/dotenv.sample) | 14 | 1.6 ms | 1.4 ms | 0.1 ms | 4.0 ms | 3.9 ms | 0.2 ms |
| dream-maker | [dream-maker.sample](tests/Fixtures/samples/dream-maker.sample) | 124 | 26.1 ms | 23.5 ms | 1.5 ms | 45.8 ms | 45.3 ms | 2.5 ms |
| edge | [edge.sample](tests/Fixtures/samples/edge.sample) | 24 | 16.3 ms | 15.5 ms | 0.7 ms | 37.5 ms | 38.0 ms | 0.9 ms |
| elixir | [elixir.sample](tests/Fixtures/samples/elixir.sample) | 183 | 110 ms | 99.9 ms | 6.1 ms | 165 ms | 152 ms | 7.8 ms |
| elm | [elm.sample](tests/Fixtures/samples/elm.sample) | 66 | 11.8 ms | 11.3 ms | 0.5 ms | 26.6 ms | 25.6 ms | 0.7 ms |
| emacs-lisp | [emacs-lisp.sample](tests/Fixtures/samples/emacs-lisp.sample) | 156 | 311 ms | 186 ms | 11.1 ms | 490 ms | 362 ms | 13.0 ms |
| erb | [erb.sample](tests/Fixtures/samples/erb.sample) | 76 | 97.5 ms | 91.5 ms | 5.6 ms | 186 ms | 181 ms | 6.5 ms |
| erlang | [erlang.sample](tests/Fixtures/samples/erlang.sample) | 201 | 116 ms | 107 ms | 6.1 ms | 313 ms | 315 ms | 8.0 ms |
| fennel | [fennel.sample](tests/Fixtures/samples/fennel.sample) | 12 | 5.2 ms | 5.0 ms | 0.3 ms | 25.4 ms | 25.1 ms | 0.4 ms |
| fish | [fish.sample](tests/Fixtures/samples/fish.sample) | 104 | 22.4 ms | 19.7 ms | 1.6 ms | 44.9 ms | 42.1 ms | 2.2 ms |
| fluent | [fluent.sample](tests/Fixtures/samples/fluent.sample) | 27 | 2.8 ms | 2.5 ms | 0.1 ms | 9.2 ms | 8.9 ms | 0.3 ms |
| fortran-fixed-form | [fortran-fixed-form.sample](tests/Fixtures/samples/fortran-fixed-form.sample) | 34 | 31.4 ms | 28.0 ms | 1.9 ms | 51.9 ms | 49.2 ms | 2.1 ms |
| fortran-free-form | [fortran-free-form.sample](tests/Fixtures/samples/fortran-free-form.sample) | 183 | 467 ms | 466 ms | 16.1 ms | 720 ms | 706 ms | 18.4 ms |
| fsharp | [fsharp.sample](tests/Fixtures/samples/fsharp.sample) | 156 | 52.8 ms | 47.4 ms | 4.0 ms | 82.9 ms | 78.9 ms | 5.6 ms |
| gdresource | [gdresource.sample](tests/Fixtures/samples/gdresource.sample) | 95 | 18.3 ms | 16.4 ms | 0.8 ms | 29.5 ms | 27.9 ms | 1.6 ms |
| gdscript | [gdscript.sample](tests/Fixtures/samples/gdscript.sample) | 57 | 23.5 ms | 21.7 ms | 1.5 ms | 46.6 ms | 45.3 ms | 1.9 ms |
| gdshader | [gdshader.sample](tests/Fixtures/samples/gdshader.sample) | 97 | 32.9 ms | 30.2 ms | 1.9 ms | 51.8 ms | 49.9 ms | 2.8 ms |
| genie | [genie.sample](tests/Fixtures/samples/genie.sample) | 109 | 16.6 ms | 15.0 ms | 0.9 ms | 28.0 ms | 26.7 ms | 1.6 ms |
| gherkin | [gherkin.sample](tests/Fixtures/samples/gherkin.sample) | 56 | 5.6 ms | 4.4 ms | 0.7 ms | 11.4 ms | 10.2 ms | 1.0 ms |
| git-commit | [git-commit.sample](tests/Fixtures/samples/git-commit.sample) | 64 | 3.1 ms | 2.9 ms | 0.2 ms | 10.5 ms | 10.3 ms | 0.5 ms |
| git-rebase | [git-rebase.sample](tests/Fixtures/samples/git-rebase.sample) | 37 | 4.7 ms | 4.1 ms | 0.1 ms | 11.4 ms | 11.0 ms | 0.5 ms |
| gleam | [gleam.sample](tests/Fixtures/samples/gleam.sample) | 89 | 17.6 ms | 16.4 ms | 1.0 ms | 28.9 ms | 27.6 ms | 1.8 ms |
| glimmer-js | [glimmer-js.sample](tests/Fixtures/samples/glimmer-js.sample) | 109 | 103 ms | 94.4 ms | 4.6 ms | 213 ms | 217 ms | 5.7 ms |
| glimmer-ts | [glimmer-ts.sample](tests/Fixtures/samples/glimmer-ts.sample) | 119 | 129 ms | 110 ms | 5.6 ms | 274 ms | 265 ms | 7.0 ms |
| glsl | [glsl.sample](tests/Fixtures/samples/glsl.sample) | 118 | 92.1 ms | 85.1 ms | 7.5 ms | 133 ms | 126 ms | 8.6 ms |
| gnuplot | [gnuplot.sample](tests/Fixtures/samples/gnuplot.sample) | 112 | 36.3 ms | 32.8 ms | 1.8 ms | 71.0 ms | 69.4 ms | 2.9 ms |
| go | [go.sample](tests/Fixtures/samples/go.sample) | 613 | 485 ms | 460 ms | 26.5 ms | 584 ms | 545 ms | 33.8 ms |
| graphql | [graphql.sample](tests/Fixtures/samples/graphql.sample) | 168 | 31.6 ms | 29.2 ms | 1.2 ms | 96.4 ms | 94.0 ms | 2.4 ms |
| groovy | [groovy.sample](tests/Fixtures/samples/groovy.sample) | 173 | 82.5 ms | 74.5 ms | 5.3 ms | 224 ms | 211 ms | 6.7 ms |
| hack | [hack.sample](tests/Fixtures/samples/hack.sample) | 205 | 121 ms | 110 ms | 9.9 ms | 197 ms | 188 ms | 12.1 ms |
| haml | [haml.sample](tests/Fixtures/samples/haml.sample) | 110 | 59.8 ms | 58.0 ms | 3.9 ms | 117 ms | 118 ms | 4.2 ms |
| handlebars | [handlebars.sample](tests/Fixtures/samples/handlebars.sample) | 86 | 42.4 ms | 38.0 ms | 1.9 ms | 90.3 ms | 89.0 ms | 2.9 ms |
| haskell | [haskell.sample](tests/Fixtures/samples/haskell.sample) | 154 | 71.5 ms | 65.2 ms | 9.7 ms | 128 ms | 122 ms | 11.1 ms |
| haxe | [haxe.sample](tests/Fixtures/samples/haxe.sample) | 172 | 119 ms | 110 ms | 6.0 ms | 243 ms | 236 ms | 8.1 ms |
| hcl | [hcl.sample](tests/Fixtures/samples/hcl.sample) | 110 | 34.8 ms | 32.7 ms | 1.5 ms | 83.0 ms | 81.7 ms | 2.4 ms |
| hjson | [hjson.sample](tests/Fixtures/samples/hjson.sample) | 92 | 9.8 ms | 14.3 ms | 0.6 ms | 23.3 ms | 61.6 ms | 1.0 ms |
| hlsl | [hlsl.sample](tests/Fixtures/samples/hlsl.sample) | 167 | 36.3 ms | 30.9 ms | 3.8 ms | 52.2 ms | 46.4 ms | 4.9 ms |
| html | [html.sample](tests/Fixtures/samples/html.sample) | 129 | 159 ms | 150 ms | 10.1 ms | 389 ms | 384 ms | 12.3 ms |
| html-derivative | [html-derivative.sample](tests/Fixtures/samples/html-derivative.sample) | 70 | 94.7 ms | 91.6 ms | 3.2 ms | 230 ms | 218 ms | 6.4 ms |
| http | [http.sample](tests/Fixtures/samples/http.sample) | 94 | 15.6 ms | 15.0 ms | 0.6 ms | 50.5 ms | 49.4 ms | 1.1 ms |
| hurl | [hurl.sample](tests/Fixtures/samples/hurl.sample) | 170 | 17.3 ms | 15.8 ms | 0.7 ms | 42.2 ms | 41.7 ms | 1.5 ms |
| hxml | [hxml.sample](tests/Fixtures/samples/hxml.sample) | 10 | 2.7 ms | 2.6 ms | 0.1 ms | 5.6 ms | 5.3 ms | 0.2 ms |
| hy | [hy.sample](tests/Fixtures/samples/hy.sample) | 33 | 1.1 ms | 3.8 ms | 0.3 ms | 2.8 ms | 7.9 ms | 0.5 ms |
| imba | [imba.sample](tests/Fixtures/samples/imba.sample) | 164 | 106 ms | 101 ms | 24.6 ms | 168 ms | 165 ms | 26.4 ms |
| ini | [ini.sample](tests/Fixtures/samples/ini.sample) | 13 | 1.0 ms | 0.9 ms | 0.0 ms | 3.4 ms | 3.4 ms | 0.1 ms |
| java | [java.sample](tests/Fixtures/samples/java.sample) | 280 | 165 ms | 147 ms | 10.9 ms | 365 ms | 342 ms | 13.5 ms |
| javascript | [javascript.sample](tests/Fixtures/samples/javascript.sample) | 710 | 643 ms | 595 ms | 38.4 ms | 1174 ms | 1134 ms | 159 ms |
| jinja | [jinja.sample](tests/Fixtures/samples/jinja.sample) | 64 | 19.4 ms | 18.2 ms | 0.9 ms | 40.6 ms | 38.6 ms | 1.7 ms |
| jison | [jison.sample](tests/Fixtures/samples/jison.sample) | 61 | 33.5 ms | 32.5 ms | 1.9 ms | 50.4 ms | 49.5 ms | 1.4 ms |
| json | [json.sample](tests/Fixtures/samples/json.sample) | 51 | 8.7 ms | 8.1 ms | 0.5 ms | 29.2 ms | 28.7 ms | 0.6 ms |
| json5 | [json5.sample](tests/Fixtures/samples/json5.sample) | 42 | 10.4 ms | 9.8 ms | 0.4 ms | 25.1 ms | 24.3 ms | 0.6 ms |
| jsonc | [jsonc.sample](tests/Fixtures/samples/jsonc.sample) | 35 | 8.5 ms | 8.0 ms | 0.3 ms | 28.3 ms | 27.8 ms | 0.5 ms |
| jsonl | [jsonl.sample](tests/Fixtures/samples/jsonl.sample) | 32 | 6.6 ms | 6.2 ms | 0.2 ms | 16.7 ms | 16.5 ms | 0.4 ms |
| jsonnet | [jsonnet.sample](tests/Fixtures/samples/jsonnet.sample) | 34 | 5.7 ms | 5.2 ms | 0.2 ms | 9.6 ms | 9.0 ms | 0.5 ms |
| jssm | [jssm.sample](tests/Fixtures/samples/jssm.sample) | 22 | 13.2 ms | 12.4 ms | 0.5 ms | 18.3 ms | 17.7 ms | 0.6 ms |
| jsx | [jsx.sample](tests/Fixtures/samples/jsx.sample) | 719 | 631 ms | 575 ms | 37.9 ms | 1322 ms | 1268 ms | 46.4 ms |
| julia | [julia.sample](tests/Fixtures/samples/julia.sample) | 157 | 73.5 ms | 67.7 ms | 4.2 ms | 109 ms | 103 ms | 5.8 ms |
| kdl | [kdl.sample](tests/Fixtures/samples/kdl.sample) | 76 | 11.1 ms | 10.3 ms | 0.5 ms | 19.8 ms | 19.1 ms | 0.9 ms |
| kotlin | [kotlin.sample](tests/Fixtures/samples/kotlin.sample) | 190 | 42.7 ms | 40.9 ms | 2.4 ms | 64.9 ms | 65.4 ms | 3.9 ms |
| kusto | [kusto.sample](tests/Fixtures/samples/kusto.sample) | 72 | 29.0 ms | 25.3 ms | 2.3 ms | 51.4 ms | 47.8 ms | 3.1 ms |
| latex | [latex.sample](tests/Fixtures/samples/latex.sample) | 204 | 105 ms | 117 ms | 24.0 ms | 206 ms | 221 ms | 26.2 ms |
| lean | [lean.sample](tests/Fixtures/samples/lean.sample) | 128 | 13.6 ms | 11.9 ms | 1.1 ms | 20.8 ms | 19.2 ms | 1.6 ms |
| less | [less.sample](tests/Fixtures/samples/less.sample) | 212 | 192 ms | 189 ms | 12.7 ms | 439 ms | 427 ms | 16.0 ms |
| liquid | [liquid.sample](tests/Fixtures/samples/liquid.sample) | 121 | 61.5 ms | 58.7 ms | 2.3 ms | 132 ms | 131 ms | 3.2 ms |
| llvm | [llvm.sample](tests/Fixtures/samples/llvm.sample) | 26 | 8.5 ms | 6.8 ms | 0.5 ms | 12.8 ms | 11.2 ms | 0.8 ms |
| log | [log.sample](tests/Fixtures/samples/log.sample) | 47 | 15.7 ms | 10.8 ms | 1.6 ms | 22.7 ms | 17.4 ms | 2.2 ms |
| logo | [logo.sample](tests/Fixtures/samples/logo.sample) | 22 | 1.9 ms | 1.7 ms | 0.1 ms | 4.6 ms | 4.3 ms | 0.2 ms |
| lua | [lua.sample](tests/Fixtures/samples/lua.sample) | 204 | 71.7 ms | 63.0 ms | 3.2 ms | 111 ms | 103 ms | 5.1 ms |
| luau | [luau.sample](tests/Fixtures/samples/luau.sample) | 97 | 39.2 ms | 35.2 ms | 1.8 ms | 65.0 ms | 61.8 ms | 2.7 ms |
| make | [make.sample](tests/Fixtures/samples/make.sample) | 117 | 22.0 ms | 21.4 ms | 0.9 ms | 55.0 ms | 55.6 ms | 1.6 ms |
| maml | [maml.sample](tests/Fixtures/samples/maml.sample) | 29 | 4.3 ms | 4.0 ms | 0.1 ms | 18.7 ms | 18.5 ms | 0.2 ms |
| markdown | [markdown.sample](tests/Fixtures/samples/markdown.sample) | 449 | 253 ms | 241 ms | 15.0 ms | 501 ms | 493 ms | 8.0 ms |
| marko | [marko.sample](tests/Fixtures/samples/marko.sample) | 125 | 163 ms | 142 ms | 9.8 ms | 256 ms | 236 ms | 11.9 ms |
| matlab | [matlab.sample](tests/Fixtures/samples/matlab.sample) | 115 | 26.8 ms | 24.5 ms | 1.3 ms | 65.7 ms | 63.5 ms | 2.1 ms |
| mdc | [mdc.sample](tests/Fixtures/samples/mdc.sample) | 34 | 12.9 ms | 11.9 ms | 19.5 ms | 28.5 ms | 29.1 ms | 20.4 ms |
| mdx | [mdx.sample](tests/Fixtures/samples/mdx.sample) | 135 | 96.9 ms | 111 ms | 4.0 ms | 195 ms | 213 ms | 4.8 ms |
| mermaid | [mermaid.sample](tests/Fixtures/samples/mermaid.sample) | 224 | 26.3 ms | 21.4 ms | 2.5 ms | 44.8 ms | 39.6 ms | 4.1 ms |
| mipsasm | [mipsasm.sample](tests/Fixtures/samples/mipsasm.sample) | 25 | 2.9 ms | 2.6 ms | 0.1 ms | 6.3 ms | 6.1 ms | 0.2 ms |
| mojo | [mojo.sample](tests/Fixtures/samples/mojo.sample) | 209 | 140 ms | 124 ms | 8.6 ms | 241 ms | 232 ms | 10.4 ms |
| move | [move.sample](tests/Fixtures/samples/move.sample) | 151 | 41.7 ms | 39.0 ms | 2.0 ms | 133 ms | 131 ms | 3.2 ms |
| narrat | [narrat.sample](tests/Fixtures/samples/narrat.sample) | 92 | 15.0 ms | 13.6 ms | 1.0 ms | 25.6 ms | 24.2 ms | 1.5 ms |
| neon | [neon.sample](tests/Fixtures/samples/neon.sample) | 97 | 12.9 ms | 13.2 ms | 0.5 ms | 27.6 ms | 26.6 ms | 1.0 ms |
| nextflow | [nextflow.sample](tests/Fixtures/samples/nextflow.sample) | 138 | 4.7 ms | 4.3 ms | 0.4 ms | 10.7 ms | 10.3 ms | 0.8 ms |
| nginx | [nginx.sample](tests/Fixtures/samples/nginx.sample) | 213 | 72.2 ms | 66.9 ms | 2.4 ms | 120 ms | 115 ms | 3.7 ms |
| nim | [nim.sample](tests/Fixtures/samples/nim.sample) | 177 | 70.4 ms | 65.1 ms | 6.3 ms | 110 ms | 102 ms | 8.0 ms |
| nix | [nix.sample](tests/Fixtures/samples/nix.sample) | 45 | 15.4 ms | 14.5 ms | 0.5 ms | 23.0 ms | 22.4 ms | 0.8 ms |
| nushell | [nushell.sample](tests/Fixtures/samples/nushell.sample) | 68 | 34.9 ms | 31.9 ms | 1.8 ms | 98.4 ms | 95.6 ms | 2.4 ms |
| objective-c | [objective-c.sample](tests/Fixtures/samples/objective-c.sample) | 177 | 143 ms | 131 ms | 10.3 ms | 288 ms | 282 ms | 12.3 ms |
| objective-cpp | [objective-cpp.sample](tests/Fixtures/samples/objective-cpp.sample) | 229 | 236 ms | 224 ms | 18.7 ms | 458 ms | 436 ms | 21.3 ms |
| ocaml | [ocaml.sample](tests/Fixtures/samples/ocaml.sample) | 140 | 91.1 ms | 81.4 ms | 6.3 ms | 118 ms | 111 ms | 8.2 ms |
| pascal | [pascal.sample](tests/Fixtures/samples/pascal.sample) | 110 | 12.1 ms | 9.3 ms | 1.8 ms | 18.8 ms | 16.3 ms | 2.3 ms |
| perl | [perl.sample](tests/Fixtures/samples/perl.sample) | 170 | 82.1 ms | 77.6 ms | 4.7 ms | 154 ms | 153 ms | 6.5 ms |
| php | [php.sample](tests/Fixtures/samples/php.sample) | 465 | 447 ms | 415 ms | 35.0 ms | 875 ms | 831 ms | 41.8 ms |
| pkl | [pkl.sample](tests/Fixtures/samples/pkl.sample) | 138 | 38.5 ms | 35.4 ms | 1.9 ms | 57.9 ms | 55.7 ms | 2.8 ms |
| plsql | [plsql.sample](tests/Fixtures/samples/plsql.sample) | 121 | 37.1 ms | 29.8 ms | 6.2 ms | 55.2 ms | 47.2 ms | 7.3 ms |
| polar | [polar.sample](tests/Fixtures/samples/polar.sample) | 150 | 24.4 ms | 22.3 ms | 1.2 ms | 46.5 ms | 45.1 ms | 2.1 ms |
| postcss | [postcss.sample](tests/Fixtures/samples/postcss.sample) | 141 | 26.6 ms | 24.0 ms | 1.4 ms | 43.5 ms | 41.1 ms | 2.4 ms |
| powerquery | [powerquery.sample](tests/Fixtures/samples/powerquery.sample) | 23 | 7.5 ms | 7.0 ms | 0.4 ms | 13.6 ms | 13.1 ms | 0.6 ms |
| powershell | [powershell.sample](tests/Fixtures/samples/powershell.sample) | 146 | 60.8 ms | 57.1 ms | 4.4 ms | 157 ms | 149 ms | 5.6 ms |
| prisma | [prisma.sample](tests/Fixtures/samples/prisma.sample) | 42 | 4.4 ms | 4.1 ms | 0.2 ms | 15.4 ms | 15.1 ms | 0.4 ms |
| prolog | [prolog.sample](tests/Fixtures/samples/prolog.sample) | 180 | 64.7 ms | 58.5 ms | 3.1 ms | 101 ms | 94.3 ms | 4.5 ms |
| proto | [proto.sample](tests/Fixtures/samples/proto.sample) | 45 | 4.7 ms | 4.4 ms | 0.2 ms | 11.0 ms | 10.7 ms | 0.4 ms |
| pug | [pug.sample](tests/Fixtures/samples/pug.sample) | 87 | 65.0 ms | 58.5 ms | 49.2 ms | 117 ms | 114 ms | 50.7 ms |
| puppet | [puppet.sample](tests/Fixtures/samples/puppet.sample) | 138 | 28.9 ms | 26.7 ms | 1.5 ms | 65.8 ms | 63.0 ms | 2.4 ms |
| purescript | [purescript.sample](tests/Fixtures/samples/purescript.sample) | 82 | 28.7 ms | 26.9 ms | 1.5 ms | 51.6 ms | 51.1 ms | 2.3 ms |
| python | [python.sample](tests/Fixtures/samples/python.sample) | 348 | 227 ms | 231 ms | 17.3 ms | 412 ms | 395 ms | 22.2 ms |
| qml | [qml.sample](tests/Fixtures/samples/qml.sample) | 27 | 23.2 ms | 22.2 ms | 1.1 ms | 35.6 ms | 34.3 ms | 1.2 ms |
| qmldir | [qmldir.sample](tests/Fixtures/samples/qmldir.sample) | 8 | 0.6 ms | 0.6 ms | 0.0 ms | 2.2 ms | 2.2 ms | 0.1 ms |
| qss | [qss.sample](tests/Fixtures/samples/qss.sample) | 37 | 4.6 ms | 4.0 ms | 0.2 ms | 9.0 ms | 8.6 ms | 0.5 ms |
| r | [r.sample](tests/Fixtures/samples/r.sample) | 106 | 93.3 ms | 74.7 ms | 8.2 ms | 138 ms | 121 ms | 9.1 ms |
| racket | [racket.sample](tests/Fixtures/samples/racket.sample) | 127 | 217 ms | 146 ms | 8.1 ms | 263 ms | 196 ms | 9.7 ms |
| raku | [raku.sample](tests/Fixtures/samples/raku.sample) | 134 | 28.7 ms | 24.9 ms | 2.2 ms | 44.1 ms | 40.2 ms | 2.9 ms |
| razor | [razor.sample](tests/Fixtures/samples/razor.sample) | 138 | 160 ms | 180 ms | 17.5 ms | 365 ms | 358 ms | 18.8 ms |
| reg | [reg.sample](tests/Fixtures/samples/reg.sample) | 27 | 1.7 ms | 1.5 ms | 0.1 ms | 5.2 ms | 4.8 ms | 0.2 ms |
| regexp | [regexp.sample](tests/Fixtures/samples/regexp.sample) | 30 | 13.6 ms | 13.0 ms | 0.7 ms | 30.8 ms | 30.0 ms | 1.1 ms |
| rel | [rel.sample](tests/Fixtures/samples/rel.sample) | 66 | 7.4 ms | 6.3 ms | 0.7 ms | 12.4 ms | 11.3 ms | 1.2 ms |
| riscv | [riscv.sample](tests/Fixtures/samples/riscv.sample) | 31 | 6.4 ms | 5.6 ms | 0.3 ms | 11.3 ms | 10.6 ms | 0.4 ms |
| rosmsg | [rosmsg.sample](tests/Fixtures/samples/rosmsg.sample) | 67 | 11.8 ms | 10.8 ms | 0.4 ms | 22.0 ms | 21.3 ms | 0.8 ms |
| rst | [rst.sample](tests/Fixtures/samples/rst.sample) | 149 | 36.3 ms | 34.6 ms | 1.6 ms | 58.2 ms | 56.6 ms | 1.5 ms |
| ruby | [ruby.sample](tests/Fixtures/samples/ruby.sample) | 329 | 288 ms | 300 ms | 19.4 ms | 441 ms | 415 ms | 21.9 ms |
| rust | [rust.sample](tests/Fixtures/samples/rust.sample) | 154 | 92.8 ms | 87.4 ms | 5.0 ms | 141 ms | 136 ms | 6.4 ms |
| sas | [sas.sample](tests/Fixtures/samples/sas.sample) | 75 | 21.8 ms | 16.4 ms | 2.8 ms | 37.1 ms | 31.5 ms | 3.6 ms |
| sass | [sass.sample](tests/Fixtures/samples/sass.sample) | 104 | 29.3 ms | 26.7 ms | 1.4 ms | 55.6 ms | 52.9 ms | 2.1 ms |
| scala | [scala.sample](tests/Fixtures/samples/scala.sample) | 218 | 150 ms | 138 ms | 7.8 ms | 208 ms | 194 ms | 10.2 ms |
| scheme | [scheme.sample](tests/Fixtures/samples/scheme.sample) | 27 | 4.5 ms | 4.1 ms | 0.2 ms | 13.4 ms | 13.0 ms | 0.3 ms |
| scss | [scss.sample](tests/Fixtures/samples/scss.sample) | 229 | 140 ms | 106 ms | 19.4 ms | 272 ms | 248 ms | 22.2 ms |
| sdbl | [sdbl.sample](tests/Fixtures/samples/sdbl.sample) | 22 | 2.9 ms | 2.3 ms | 0.6 ms | 5.3 ms | 4.7 ms | 0.7 ms |
| shaderlab | [shaderlab.sample](tests/Fixtures/samples/shaderlab.sample) | 61 | 11.5 ms | 10.2 ms | 0.9 ms | 18.1 ms | 16.9 ms | 1.2 ms |
| shellscript | [shellscript.sample](tests/Fixtures/samples/shellscript.sample) | 200 | 111 ms | 104 ms | 7.3 ms | 293 ms | 289 ms | 8.9 ms |
| shellsession | [shellsession.sample](tests/Fixtures/samples/shellsession.sample) | 14 | 2.4 ms | 2.0 ms | 0.0 ms | 5.6 ms | 4.8 ms | 0.5 ms |
| smalltalk | [smalltalk.sample](tests/Fixtures/samples/smalltalk.sample) | 12 | 3.6 ms | 3.3 ms | 0.2 ms | 12.9 ms | 12.5 ms | 0.2 ms |
| solidity | [solidity.sample](tests/Fixtures/samples/solidity.sample) | 173 | 103 ms | 98.6 ms | 5.7 ms | 135 ms | 130 ms | 6.9 ms |
| soy | [soy.sample](tests/Fixtures/samples/soy.sample) | 89 | 33.7 ms | 31.7 ms | 1.5 ms | 63.3 ms | 60.7 ms | 2.2 ms |
| sparql | [sparql.sample](tests/Fixtures/samples/sparql.sample) | 8 | 1.5 ms | 1.3 ms | 0.1 ms | 3.7 ms | 3.6 ms | 0.2 ms |
| splunk | [splunk.sample](tests/Fixtures/samples/splunk.sample) | 16 | 4.1 ms | 3.5 ms | 0.4 ms | 7.8 ms | 7.2 ms | 0.6 ms |
| sql | [sql.sample](tests/Fixtures/samples/sql.sample) | 135 | 68.7 ms | 48.3 ms | 10.2 ms | 95.3 ms | 74.7 ms | 11.7 ms |
| ssh-config | [ssh-config.sample](tests/Fixtures/samples/ssh-config.sample) | 48 | 4.6 ms | 4.1 ms | 0.2 ms | 8.2 ms | 7.7 ms | 0.4 ms |
| stata | [stata.sample](tests/Fixtures/samples/stata.sample) | 143 | 94.6 ms | 82.2 ms | 6.0 ms | 128 ms | 115 ms | 7.4 ms |
| stylus | [stylus.sample](tests/Fixtures/samples/stylus.sample) | 133 | 53.8 ms | 47.5 ms | 3.0 ms | 114 ms | 108 ms | 3.9 ms |
| svelte | [svelte.sample](tests/Fixtures/samples/svelte.sample) | 137 | 956 ms | 1026 ms | 15.4 ms | 1239 ms | 1292 ms | 17.2 ms |
| swift | [swift.sample](tests/Fixtures/samples/swift.sample) | 502 | 460 ms | 423 ms | 28.3 ms | 691 ms | 724 ms | 35.0 ms |
| system-verilog | [system-verilog.sample](tests/Fixtures/samples/system-verilog.sample) | 184 | 83.2 ms | 77.6 ms | 8.7 ms | 131 ms | 135 ms | 10.3 ms |
| systemd | [systemd.sample](tests/Fixtures/samples/systemd.sample) | 22 | 1.8 ms | 1.7 ms | 0.1 ms | 4.0 ms | 3.8 ms | 0.2 ms |
| talonscript | [talonscript.sample](tests/Fixtures/samples/talonscript.sample) | 120 | 17.7 ms | 16.3 ms | 0.8 ms | 33.7 ms | 30.2 ms | 1.4 ms |
| tasl | [tasl.sample](tests/Fixtures/samples/tasl.sample) | 26 | 2.8 ms | 2.6 ms | 0.1 ms | 7.2 ms | 7.2 ms | 0.2 ms |
| tcl | [tcl.sample](tests/Fixtures/samples/tcl.sample) | 162 | 17.7 ms | 15.7 ms | 1.0 ms | 33.8 ms | 33.8 ms | 2.1 ms |
| templ | [templ.sample](tests/Fixtures/samples/templ.sample) | 113 | 42.0 ms | 89.9 ms | 2.7 ms | 141 ms | 228 ms | 3.7 ms |
| terraform | [terraform.sample](tests/Fixtures/samples/terraform.sample) | 162 | 49.4 ms | 45.9 ms | 2.2 ms | 110 ms | 104 ms | 3.4 ms |
| tex | [tex.sample](tests/Fixtures/samples/tex.sample) | 30 | 4.0 ms | 3.7 ms | 0.2 ms | 10.5 ms | 10.1 ms | 0.4 ms |
| toml | [toml.sample](tests/Fixtures/samples/toml.sample) | 26 | 4.2 ms | 3.9 ms | 0.2 ms | 12.9 ms | 12.5 ms | 0.3 ms |
| ts-tags | [ts-tags.sample](tests/Fixtures/samples/ts-tags.sample) | 50 | 20.0 ms | 19.3 ms | 0.7 ms | 38.7 ms | 38.2 ms | 4.4 ms |
| tsv | [tsv.sample](tests/Fixtures/samples/tsv.sample) | 6 | 0.6 ms | 0.5 ms | 0.0 ms | 2.0 ms | 1.9 ms | 0.1 ms |
| tsx | [tsx.sample](tests/Fixtures/samples/tsx.sample) | 518 | 525 ms | 478 ms | 31.8 ms | 1173 ms | 1145 ms | 39.3 ms |
| turtle | [turtle.sample](tests/Fixtures/samples/turtle.sample) | 16 | 2.5 ms | 2.2 ms | 0.1 ms | 6.3 ms | 6.0 ms | 0.3 ms |
| twig | [twig.sample](tests/Fixtures/samples/twig.sample) | 120 | 63.6 ms | 57.4 ms | 3.9 ms | 145 ms | 141 ms | 5.1 ms |
| txt | [txt.sample](tests/Fixtures/samples/txt.sample) | 33 | 0.4 ms | 0.4 ms | 0.1 ms | 1.1 ms | 1.1 ms | 0.2 ms |
| typescript | [typescript.sample](tests/Fixtures/samples/typescript.sample) | 530 | 530 ms | 492 ms | 32.4 ms | 1014 ms | 1015 ms | 96.0 ms |
| typespec | [typespec.sample](tests/Fixtures/samples/typespec.sample) | 36 | 10.4 ms | 9.8 ms | 0.8 ms | 35.0 ms | 34.1 ms | 0.6 ms |
| typst | [typst.sample](tests/Fixtures/samples/typst.sample) | 28 | 8.1 ms | 8.7 ms | 0.4 ms | 18.0 ms | 19.3 ms | 0.6 ms |
| v | [v.sample](tests/Fixtures/samples/v.sample) | 203 | 77.7 ms | 70.4 ms | 4.1 ms | 109 ms | 102 ms | 5.5 ms |
| vala | [vala.sample](tests/Fixtures/samples/vala.sample) | 171 | 26.6 ms | 23.8 ms | 1.5 ms | 43.4 ms | 40.2 ms | 2.7 ms |
| vb | [vb.sample](tests/Fixtures/samples/vb.sample) | 39 | 10.8 ms | 9.3 ms | 0.9 ms | 20.6 ms | 19.1 ms | 1.2 ms |
| verilog | [verilog.sample](tests/Fixtures/samples/verilog.sample) | 31 | 4.5 ms | 4.2 ms | 0.3 ms | 9.6 ms | 9.3 ms | 0.4 ms |
| vhdl | [vhdl.sample](tests/Fixtures/samples/vhdl.sample) | 216 | 84.1 ms | 75.9 ms | 35.2 ms | 122 ms | 114 ms | 39.6 ms |
| viml | [viml.sample](tests/Fixtures/samples/viml.sample) | 126 | 43.1 ms | 34.9 ms | 4.1 ms | 61.7 ms | 53.2 ms | 4.9 ms |
| vue | [vue.sample](tests/Fixtures/samples/vue.sample) | 188 | 154 ms | 117 ms | 8.1 ms | 387 ms | 377 ms | 15.4 ms |
| vue-html | [vue-html.sample](tests/Fixtures/samples/vue-html.sample) | 71 | 51.1 ms | 46.7 ms | 3.3 ms | 98.2 ms | 94.7 ms | 4.6 ms |
| vue-vine | [vue-vine.sample](tests/Fixtures/samples/vue-vine.sample) | 253 | 242 ms | 225 ms | 15.1 ms | 658 ms | 657 ms | 18.5 ms |
| vyper | [vyper.sample](tests/Fixtures/samples/vyper.sample) | 192 | 128 ms | 117 ms | 8.6 ms | 182 ms | 173 ms | 9.9 ms |
| wasm | [wasm.sample](tests/Fixtures/samples/wasm.sample) | 175 | 90.1 ms | 84.7 ms | 4.4 ms | 115 ms | 111 ms | 6.4 ms |
| wgsl | [wgsl.sample](tests/Fixtures/samples/wgsl.sample) | 19 | 6.8 ms | 6.3 ms | 0.3 ms | 13.0 ms | 12.5 ms | 0.5 ms |
| wikitext | [wikitext.sample](tests/Fixtures/samples/wikitext.sample) | 33 | 31.9 ms | 30.3 ms | 2.3 ms | 43.3 ms | 41.5 ms | 2.9 ms |
| wit | [wit.sample](tests/Fixtures/samples/wit.sample) | 59 | 13.8 ms | 13.3 ms | 0.4 ms | 72.7 ms | 72.5 ms | 0.8 ms |
| wolfram | [wolfram.sample](tests/Fixtures/samples/wolfram.sample) | 176 | 818 ms | 782 ms | 49.5 ms | 946 ms | 904 ms | 54.1 ms |
| xml | [xml.sample](tests/Fixtures/samples/xml.sample) | 123 | 19.4 ms | 17.9 ms | 0.8 ms | 33.5 ms | 31.8 ms | 1.8 ms |
| xsl | [xsl.sample](tests/Fixtures/samples/xsl.sample) | 66 | 9.1 ms | 8.4 ms | 0.3 ms | 21.0 ms | 20.2 ms | 0.7 ms |
| yaml | [yaml.sample](tests/Fixtures/samples/yaml.sample) | 87 | 25.1 ms | 22.4 ms | 1.4 ms | 60.2 ms | 56.9 ms | 2.0 ms |
| zenscript | [zenscript.sample](tests/Fixtures/samples/zenscript.sample) | 44 | 6.5 ms | 5.9 ms | 0.4 ms | 13.1 ms | 12.6 ms | 0.7 ms |
| zig | [zig.sample](tests/Fixtures/samples/zig.sample) | 26 | 11.0 ms | 10.4 ms | 0.5 ms | 17.0 ms | 16.3 ms | 0.8 ms |
