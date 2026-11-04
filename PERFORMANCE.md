# Performance

This report times how long Phiki takes to tokenize and highlight the sample of each bundled grammar with each of its regex engines, Oniguruma and PCRE. It compares tokenizing with [vscode-textmate](https://github.com/microsoft/vscode-textmate), the tokenizer used by VS Code and Shiki, and highlighting with [Shiki](https://shiki.style), using its Oniguruma engine.

Regenerate it with `composer performance` after changing an engine, the tokenizer, the highlighter or the HTML output. It needs Node.js and `npm ci`. Timings depend on the machine, so compare reports generated on the same one.

- **Tokenize** is `Phiki::codeToTokens()`, and a call to `tokenizeLine()` for each line in vscode-textmate.
- **Highlight** is `Phiki::codeToHtml()` with the `github-light` theme, rendered to a string, and `codeToHtml()` in Shiki.
- Every implementation uses Phiki's bundled grammars and theme, so they all work from the same input.
- Each time is the median of 5 runs in PHP and 10 in Node.js. Before timing, each sample is tokenized and highlighted once in PHP and 10 times in Node.js, so V8 has optimized the code. Loading and compiling the grammars is not counted.
- **Compared with JavaScript** is how many times longer Phiki takes than vscode-textmate or Shiki for all 227 samples together. **Typical sample** is the geometric mean of that for each sample, so the largest samples don't outweigh the rest.

Generated with PHP 8.4.27 (OPcache disabled, JIT disabled), PCRE2 10.42, Oniguruma 6.9.8 and Node.js v26.12.1, on Intel(R) Xeon(R) Processor @ 2.60GHz.

## Summary

| Task | Implementation | All samples | Compared with JavaScript | Typical sample compared with JavaScript |
| --- | --- | ---: | ---: | ---: |
| Tokenize | vscode-textmate | 1723 ms | – | – |
| Tokenize | Phiki, Oniguruma | 7754 ms | 4.5× | 4.6× |
| Tokenize | Phiki, PCRE | 6406 ms | 3.7× | 3.7× |
| Highlight | Shiki | 2425 ms | – | – |
| Highlight | Phiki, Oniguruma | 11173 ms | 4.6× | 4.7× |
| Highlight | Phiki, PCRE | 9726 ms | 4.0× | 4.1× |

## Grammars

| Grammar | Sample | Lines | Tokenize: Oniguruma | Tokenize: PCRE | Tokenize: vscode-textmate | Highlight: Oniguruma | Highlight: PCRE | Highlight: Shiki |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| abap | [abap.sample](tests/Fixtures/samples/abap.sample) | 152 | 22.2 ms | 9.4 ms | 10.7 ms | 26.7 ms | 13.9 ms | 12.5 ms |
| actionscript-3 | [actionscript-3.sample](tests/Fixtures/samples/actionscript-3.sample) | 154 | 12.2 ms | 9.6 ms | 3.2 ms | 23.5 ms | 21.6 ms | 5.4 ms |
| ada | [ada.sample](tests/Fixtures/samples/ada.sample) | 226 | 29.2 ms | 23.3 ms | 5.9 ms | 64.6 ms | 58.5 ms | 8.9 ms |
| angular-html | [angular-html.sample](tests/Fixtures/samples/angular-html.sample) | 19 | 3.5 ms | 2.8 ms | 0.4 ms | 7.3 ms | 6.6 ms | 1.2 ms |
| angular-ts | [angular-ts.sample](tests/Fixtures/samples/angular-ts.sample) | 209 | 77.4 ms | 67.7 ms | 16.6 ms | 138 ms | 126 ms | 62.3 ms |
| antlers | [antlers.sample](tests/Fixtures/samples/antlers.sample) | 105 | 57.7 ms | 49.6 ms | 7.4 ms | 72.0 ms | 61.1 ms | 9.0 ms |
| apache | [apache.sample](tests/Fixtures/samples/apache.sample) | 148 | 13.0 ms | 8.7 ms | 3.9 ms | 16.4 ms | 12.2 ms | 4.9 ms |
| apex | [apex.sample](tests/Fixtures/samples/apex.sample) | 213 | 44.0 ms | 39.0 ms | 9.6 ms | 55.7 ms | 50.9 ms | 12.4 ms |
| apl | [apl.sample](tests/Fixtures/samples/apl.sample) | 164 | 39.9 ms | 34.7 ms | 8.8 ms | 51.7 ms | 47.6 ms | 11.3 ms |
| applescript | [applescript.sample](tests/Fixtures/samples/applescript.sample) | 126 | 22.3 ms | 17.5 ms | 16.4 ms | 48.6 ms | 44.0 ms | 18.2 ms |
| ara | [ara.sample](tests/Fixtures/samples/ara.sample) | 186 | 15.6 ms | 12.2 ms | 4.3 ms | 21.3 ms | 18.0 ms | 6.6 ms |
| asciidoc | [asciidoc.sample](tests/Fixtures/samples/asciidoc.sample) | 249 | 45.3 ms | 40.0 ms | 9.1 ms | 66.4 ms | 59.2 ms | 5.6 ms |
| asm | [asm.sample](tests/Fixtures/samples/asm.sample) | 238 | 80.1 ms | 61.8 ms | 22.5 ms | 86.6 ms | 69.5 ms | 26.0 ms |
| astro | [astro.sample](tests/Fixtures/samples/astro.sample) | 122 | 157 ms | 149 ms | 15.4 ms | 208 ms | 201 ms | 17.1 ms |
| awk | [awk.sample](tests/Fixtures/samples/awk.sample) | 25 | 1.1 ms | 0.9 ms | 0.2 ms | 2.0 ms | 1.9 ms | 0.4 ms |
| ballerina | [ballerina.sample](tests/Fixtures/samples/ballerina.sample) | 227 | 46.2 ms | 40.0 ms | 10.5 ms | 83.0 ms | 75.8 ms | 13.8 ms |
| bat | [bat.sample](tests/Fixtures/samples/bat.sample) | 82 | 9.9 ms | 7.0 ms | 2.4 ms | 17.2 ms | 14.3 ms | 3.3 ms |
| beancount | [beancount.sample](tests/Fixtures/samples/beancount.sample) | 81 | 5.7 ms | 4.8 ms | 0.9 ms | 12.4 ms | 11.4 ms | 2.2 ms |
| berry | [berry.sample](tests/Fixtures/samples/berry.sample) | 60 | 2.7 ms | 2.2 ms | 0.7 ms | 4.4 ms | 3.8 ms | 1.2 ms |
| bibtex | [bibtex.sample](tests/Fixtures/samples/bibtex.sample) | 29 | 0.9 ms | 0.8 ms | 0.1 ms | 2.2 ms | 2.1 ms | 0.5 ms |
| blade | [blade.sample](tests/Fixtures/samples/blade.sample) | 213 | 85.6 ms | 73.0 ms | 16.0 ms | 129 ms | 118 ms | 18.9 ms |
| bsl | [bsl.sample](tests/Fixtures/samples/bsl.sample) | 87 | 16.5 ms | 7.9 ms | 7.4 ms | 19.4 ms | 11.4 ms | 8.8 ms |
| c | [c.sample](tests/Fixtures/samples/c.sample) | 437 | 90.3 ms | 74.1 ms | 21.5 ms | 139 ms | 115 ms | 27.0 ms |
| cadence | [cadence.sample](tests/Fixtures/samples/cadence.sample) | 178 | 29.1 ms | 25.1 ms | 6.0 ms | 60.2 ms | 55.3 ms | 8.2 ms |
| cairo | [cairo.sample](tests/Fixtures/samples/cairo.sample) | 156 | 5.0 ms | 4.0 ms | 1.3 ms | 9.4 ms | 8.5 ms | 2.4 ms |
| clarity | [clarity.sample](tests/Fixtures/samples/clarity.sample) | 53 | 3.3 ms | 2.6 ms | 0.5 ms | 8.0 ms | 7.4 ms | 1.0 ms |
| clojure | [clojure.sample](tests/Fixtures/samples/clojure.sample) | 14 | 2.1 ms | 1.8 ms | 0.5 ms | 5.5 ms | 5.1 ms | 0.7 ms |
| cmake | [cmake.sample](tests/Fixtures/samples/cmake.sample) | 139 | 9.8 ms | 5.1 ms | 3.4 ms | 12.3 ms | 7.7 ms | 4.5 ms |
| cobol | [cobol.sample](tests/Fixtures/samples/cobol.sample) | 228 | 86.9 ms | 37.5 ms | 43.5 ms | 94.6 ms | 46.3 ms | 45.1 ms |
| codeowners | [codeowners.sample](tests/Fixtures/samples/codeowners.sample) | 8 | 0.1 ms | 0.1 ms | 0.0 ms | 0.4 ms | 0.4 ms | 0.1 ms |
| codeql | [codeql.sample](tests/Fixtures/samples/codeql.sample) | 103 | 20.4 ms | 16.9 ms | 4.2 ms | 31.5 ms | 28.6 ms | 5.6 ms |
| coffee | [coffee.sample](tests/Fixtures/samples/coffee.sample) | 151 | 31.0 ms | 23.8 ms | 7.2 ms | 46.2 ms | 41.2 ms | 15.2 ms |
| common-lisp | [common-lisp.sample](tests/Fixtures/samples/common-lisp.sample) | 29 | 8.2 ms | 3.1 ms | 6.1 ms | 10.2 ms | 5.1 ms | 5.5 ms |
| coq | [coq.sample](tests/Fixtures/samples/coq.sample) | 37 | 2.4 ms | 1.7 ms | 0.6 ms | 3.9 ms | 4.1 ms | 1.0 ms |
| cpp | [cpp.sample](tests/Fixtures/samples/cpp.sample) | 627 | 525 ms | 347 ms | 132 ms | 685 ms | 518 ms | 148 ms |
| crystal | [crystal.sample](tests/Fixtures/samples/crystal.sample) | 184 | 32.9 ms | 27.3 ms | 8.0 ms | 48.2 ms | 39.6 ms | 9.9 ms |
| csharp | [csharp.sample](tests/Fixtures/samples/csharp.sample) | 503 | 144 ms | 118 ms | 23.1 ms | 209 ms | 150 ms | 32.2 ms |
| css | [css.sample](tests/Fixtures/samples/css.sample) | 236 | 39.3 ms | 22.0 ms | 12.2 ms | 69.5 ms | 50.9 ms | 13.9 ms |
| csv | [csv.sample](tests/Fixtures/samples/csv.sample) | 5 | 0.2 ms | 0.1 ms | 0.0 ms | 0.5 ms | 0.5 ms | 0.1 ms |
| cue | [cue.sample](tests/Fixtures/samples/cue.sample) | 192 | 22.1 ms | 19.0 ms | 5.6 ms | 39.7 ms | 35.8 ms | 7.7 ms |
| cypher | [cypher.sample](tests/Fixtures/samples/cypher.sample) | 88 | 9.3 ms | 7.1 ms | 3.1 ms | 14.9 ms | 12.7 ms | 4.6 ms |
| d | [d.sample](tests/Fixtures/samples/d.sample) | 214 | 73.2 ms | 67.4 ms | 17.1 ms | 84.0 ms | 91.4 ms | 19.8 ms |
| dart | [dart.sample](tests/Fixtures/samples/dart.sample) | 231 | 21.7 ms | 17.7 ms | 5.4 ms | 30.5 ms | 26.5 ms | 8.2 ms |
| dax | [dax.sample](tests/Fixtures/samples/dax.sample) | 20 | 1.0 ms | 0.7 ms | 0.2 ms | 1.6 ms | 1.4 ms | 0.3 ms |
| desktop | [desktop.sample](tests/Fixtures/samples/desktop.sample) | 22 | 1.2 ms | 1.0 ms | 0.4 ms | 1.8 ms | 1.8 ms | 0.6 ms |
| diff | [diff.sample](tests/Fixtures/samples/diff.sample) | 28 | 0.6 ms | 0.4 ms | 0.5 ms | 1.1 ms | 1.0 ms | 0.6 ms |
| djot | [djot.sample](tests/Fixtures/samples/djot.sample) | 126 | 6.7 ms | 5.9 ms | 1.2 ms | 13.5 ms | 12.7 ms | 2.0 ms |
| docker | [docker.sample](tests/Fixtures/samples/docker.sample) | 19 | 0.4 ms | 0.3 ms | 0.1 ms | 0.8 ms | 0.7 ms | 0.2 ms |
| dotenv | [dotenv.sample](tests/Fixtures/samples/dotenv.sample) | 14 | 0.5 ms | 0.5 ms | 0.1 ms | 1.1 ms | 1.0 ms | 0.2 ms |
| dream-maker | [dream-maker.sample](tests/Fixtures/samples/dream-maker.sample) | 124 | 8.8 ms | 6.8 ms | 2.0 ms | 13.9 ms | 12.3 ms | 3.3 ms |
| edge | [edge.sample](tests/Fixtures/samples/edge.sample) | 24 | 5.7 ms | 4.8 ms | 0.9 ms | 10.0 ms | 9.2 ms | 1.2 ms |
| elixir | [elixir.sample](tests/Fixtures/samples/elixir.sample) | 183 | 29.2 ms | 25.6 ms | 7.7 ms | 42.5 ms | 39.0 ms | 10.1 ms |
| elm | [elm.sample](tests/Fixtures/samples/elm.sample) | 66 | 3.5 ms | 3.1 ms | 0.6 ms | 6.8 ms | 6.4 ms | 1.0 ms |
| emacs-lisp | [emacs-lisp.sample](tests/Fixtures/samples/emacs-lisp.sample) | 156 | 312 ms | 114 ms | 13.9 ms | 345 ms | 151 ms | 16.7 ms |
| erb | [erb.sample](tests/Fixtures/samples/erb.sample) | 76 | 35.8 ms | 30.0 ms | 8.9 ms | 55.6 ms | 48.0 ms | 11.1 ms |
| erlang | [erlang.sample](tests/Fixtures/samples/erlang.sample) | 201 | 37.5 ms | 31.4 ms | 7.8 ms | 78.1 ms | 73.1 ms | 15.4 ms |
| fennel | [fennel.sample](tests/Fixtures/samples/fennel.sample) | 12 | 1.6 ms | 1.4 ms | 0.3 ms | 5.8 ms | 5.6 ms | 0.5 ms |
| fish | [fish.sample](tests/Fixtures/samples/fish.sample) | 104 | 8.6 ms | 6.0 ms | 2.0 ms | 13.9 ms | 11.4 ms | 3.0 ms |
| fluent | [fluent.sample](tests/Fixtures/samples/fluent.sample) | 27 | 1.0 ms | 0.8 ms | 0.1 ms | 2.4 ms | 2.2 ms | 0.4 ms |
| fortran-fixed-form | [fortran-fixed-form.sample](tests/Fixtures/samples/fortran-fixed-form.sample) | 34 | 9.8 ms | 8.5 ms | 3.4 ms | 14.6 ms | 13.1 ms | 2.6 ms |
| fortran-free-form | [fortran-free-form.sample](tests/Fixtures/samples/fortran-free-form.sample) | 183 | 139 ms | 148 ms | 19.2 ms | 213 ms | 201 ms | 21.6 ms |
| fsharp | [fsharp.sample](tests/Fixtures/samples/fsharp.sample) | 156 | 18.1 ms | 14.1 ms | 4.6 ms | 32.1 ms | 22.6 ms | 7.2 ms |
| gdresource | [gdresource.sample](tests/Fixtures/samples/gdresource.sample) | 95 | 6.6 ms | 5.2 ms | 1.0 ms | 9.9 ms | 8.6 ms | 2.4 ms |
| gdscript | [gdscript.sample](tests/Fixtures/samples/gdscript.sample) | 57 | 8.1 ms | 6.3 ms | 2.1 ms | 13.4 ms | 11.4 ms | 2.6 ms |
| gdshader | [gdshader.sample](tests/Fixtures/samples/gdshader.sample) | 97 | 10.2 ms | 8.4 ms | 2.7 ms | 15.2 ms | 13.3 ms | 3.7 ms |
| genie | [genie.sample](tests/Fixtures/samples/genie.sample) | 109 | 5.4 ms | 4.1 ms | 1.2 ms | 8.7 ms | 7.4 ms | 2.1 ms |
| gherkin | [gherkin.sample](tests/Fixtures/samples/gherkin.sample) | 56 | 2.8 ms | 1.5 ms | 0.8 ms | 4.4 ms | 3.1 ms | 1.2 ms |
| git-commit | [git-commit.sample](tests/Fixtures/samples/git-commit.sample) | 64 | 1.0 ms | 0.9 ms | 0.3 ms | 2.7 ms | 2.6 ms | 0.6 ms |
| git-rebase | [git-rebase.sample](tests/Fixtures/samples/git-rebase.sample) | 37 | 1.5 ms | 1.3 ms | 0.2 ms | 3.1 ms | 2.9 ms | 0.6 ms |
| gleam | [gleam.sample](tests/Fixtures/samples/gleam.sample) | 89 | 5.3 ms | 4.4 ms | 1.3 ms | 8.4 ms | 7.5 ms | 2.5 ms |
| glimmer-js | [glimmer-js.sample](tests/Fixtures/samples/glimmer-js.sample) | 109 | 38.8 ms | 33.1 ms | 6.0 ms | 61.9 ms | 56.1 ms | 7.4 ms |
| glimmer-ts | [glimmer-ts.sample](tests/Fixtures/samples/glimmer-ts.sample) | 119 | 60.6 ms | 37.7 ms | 7.5 ms | 93.9 ms | 85.1 ms | 8.9 ms |
| glsl | [glsl.sample](tests/Fixtures/samples/glsl.sample) | 118 | 31.0 ms | 24.7 ms | 9.1 ms | 40.6 ms | 36.0 ms | 10.3 ms |
| gnuplot | [gnuplot.sample](tests/Fixtures/samples/gnuplot.sample) | 112 | 12.5 ms | 9.9 ms | 2.3 ms | 20.6 ms | 17.8 ms | 3.8 ms |
| go | [go.sample](tests/Fixtures/samples/go.sample) | 613 | 163 ms | 141 ms | 32.8 ms | 191 ms | 170 ms | 48.1 ms |
| graphql | [graphql.sample](tests/Fixtures/samples/graphql.sample) | 168 | 11.0 ms | 9.4 ms | 2.0 ms | 24.7 ms | 24.0 ms | 4.5 ms |
| groovy | [groovy.sample](tests/Fixtures/samples/groovy.sample) | 173 | 27.3 ms | 22.1 ms | 6.7 ms | 55.5 ms | 51.4 ms | 8.5 ms |
| hack | [hack.sample](tests/Fixtures/samples/hack.sample) | 205 | 43.8 ms | 33.6 ms | 13.1 ms | 62.1 ms | 51.7 ms | 15.8 ms |
| haml | [haml.sample](tests/Fixtures/samples/haml.sample) | 110 | 21.2 ms | 17.9 ms | 6.0 ms | 34.0 ms | 30.6 ms | 5.4 ms |
| handlebars | [handlebars.sample](tests/Fixtures/samples/handlebars.sample) | 86 | 16.9 ms | 12.7 ms | 2.8 ms | 27.4 ms | 24.1 ms | 4.2 ms |
| haskell | [haskell.sample](tests/Fixtures/samples/haskell.sample) | 154 | 25.1 ms | 19.8 ms | 11.7 ms | 38.5 ms | 33.6 ms | 13.9 ms |
| haxe | [haxe.sample](tests/Fixtures/samples/haxe.sample) | 172 | 39.8 ms | 32.8 ms | 7.4 ms | 67.5 ms | 61.9 ms | 10.0 ms |
| hcl | [hcl.sample](tests/Fixtures/samples/hcl.sample) | 110 | 11.1 ms | 9.5 ms | 2.0 ms | 21.6 ms | 25.0 ms | 3.4 ms |
| hjson | [hjson.sample](tests/Fixtures/samples/hjson.sample) | 92 | 4.2 ms | 4.6 ms | 0.8 ms | 6.5 ms | 14.1 ms | 1.5 ms |
| hlsl | [hlsl.sample](tests/Fixtures/samples/hlsl.sample) | 167 | 12.4 ms | 8.5 ms | 4.9 ms | 16.7 ms | 12.8 ms | 6.4 ms |
| html | [html.sample](tests/Fixtures/samples/html.sample) | 129 | 68.1 ms | 59.4 ms | 12.0 ms | 114 ms | 107 ms | 14.2 ms |
| html-derivative | [html-derivative.sample](tests/Fixtures/samples/html-derivative.sample) | 70 | 31.6 ms | 27.7 ms | 3.9 ms | 57.4 ms | 53.2 ms | 8.3 ms |
| http | [http.sample](tests/Fixtures/samples/http.sample) | 94 | 5.3 ms | 4.6 ms | 0.8 ms | 12.5 ms | 12.0 ms | 1.5 ms |
| hurl | [hurl.sample](tests/Fixtures/samples/hurl.sample) | 170 | 5.7 ms | 4.9 ms | 0.9 ms | 11.5 ms | 10.8 ms | 2.0 ms |
| hxml | [hxml.sample](tests/Fixtures/samples/hxml.sample) | 10 | 0.9 ms | 0.8 ms | 0.1 ms | 1.6 ms | 1.4 ms | 0.2 ms |
| hy | [hy.sample](tests/Fixtures/samples/hy.sample) | 33 | 0.4 ms | 1.1 ms | 0.3 ms | 0.9 ms | 2.2 ms | 0.7 ms |
| imba | [imba.sample](tests/Fixtures/samples/imba.sample) | 164 | 34.3 ms | 27.3 ms | 27.2 ms | 49.2 ms | 42.6 ms | 29.4 ms |
| ini | [ini.sample](tests/Fixtures/samples/ini.sample) | 13 | 0.3 ms | 0.3 ms | 0.1 ms | 0.9 ms | 0.9 ms | 0.1 ms |
| java | [java.sample](tests/Fixtures/samples/java.sample) | 280 | 56.5 ms | 45.0 ms | 13.2 ms | 98.1 ms | 88.4 ms | 20.2 ms |
| javascript | [javascript.sample](tests/Fixtures/samples/javascript.sample) | 710 | 277 ms | 239 ms | 48.7 ms | 391 ms | 362 ms | 204 ms |
| jinja | [jinja.sample](tests/Fixtures/samples/jinja.sample) | 64 | 6.2 ms | 5.3 ms | 1.1 ms | 11.2 ms | 10.3 ms | 2.2 ms |
| jison | [jison.sample](tests/Fixtures/samples/jison.sample) | 61 | 9.8 ms | 9.1 ms | 1.5 ms | 13.4 ms | 12.6 ms | 2.0 ms |
| json | [json.sample](tests/Fixtures/samples/json.sample) | 51 | 2.9 ms | 2.5 ms | 0.4 ms | 7.3 ms | 6.9 ms | 0.8 ms |
| json5 | [json5.sample](tests/Fixtures/samples/json5.sample) | 42 | 3.3 ms | 2.9 ms | 0.5 ms | 6.5 ms | 6.1 ms | 0.8 ms |
| jsonc | [jsonc.sample](tests/Fixtures/samples/jsonc.sample) | 35 | 2.5 ms | 2.2 ms | 0.3 ms | 6.6 ms | 6.3 ms | 0.7 ms |
| jsonl | [jsonl.sample](tests/Fixtures/samples/jsonl.sample) | 32 | 2.1 ms | 1.8 ms | 0.3 ms | 4.4 ms | 4.1 ms | 0.6 ms |
| jsonnet | [jsonnet.sample](tests/Fixtures/samples/jsonnet.sample) | 34 | 1.8 ms | 1.4 ms | 0.3 ms | 2.8 ms | 2.4 ms | 0.7 ms |
| jssm | [jssm.sample](tests/Fixtures/samples/jssm.sample) | 22 | 3.1 ms | 2.7 ms | 0.6 ms | 4.5 ms | 4.2 ms | 0.9 ms |
| jsx | [jsx.sample](tests/Fixtures/samples/jsx.sample) | 719 | 271 ms | 239 ms | 46.9 ms | 406 ms | 370 ms | 63.2 ms |
| julia | [julia.sample](tests/Fixtures/samples/julia.sample) | 157 | 26.8 ms | 19.8 ms | 5.6 ms | 35.2 ms | 27.9 ms | 7.2 ms |
| kdl | [kdl.sample](tests/Fixtures/samples/kdl.sample) | 76 | 3.5 ms | 2.9 ms | 0.7 ms | 5.8 ms | 5.2 ms | 1.2 ms |
| kotlin | [kotlin.sample](tests/Fixtures/samples/kotlin.sample) | 190 | 13.4 ms | 11.4 ms | 3.3 ms | 19.3 ms | 18.0 ms | 5.3 ms |
| kusto | [kusto.sample](tests/Fixtures/samples/kusto.sample) | 72 | 10.3 ms | 7.4 ms | 2.9 ms | 15.7 ms | 12.6 ms | 4.0 ms |
| latex | [latex.sample](tests/Fixtures/samples/latex.sample) | 204 | 36.7 ms | 50.7 ms | 31.1 ms | 57.6 ms | 72.6 ms | 32.6 ms |
| lean | [lean.sample](tests/Fixtures/samples/lean.sample) | 128 | 4.9 ms | 3.5 ms | 1.3 ms | 7.0 ms | 5.5 ms | 2.4 ms |
| less | [less.sample](tests/Fixtures/samples/less.sample) | 212 | 68.3 ms | 56.7 ms | 15.0 ms | 136 ms | 125 ms | 19.6 ms |
| liquid | [liquid.sample](tests/Fixtures/samples/liquid.sample) | 121 | 19.7 ms | 17.1 ms | 3.1 ms | 35.4 ms | 32.1 ms | 4.4 ms |
| llvm | [llvm.sample](tests/Fixtures/samples/llvm.sample) | 26 | 3.9 ms | 2.8 ms | 0.6 ms | 5.2 ms | 3.9 ms | 1.1 ms |
| log | [log.sample](tests/Fixtures/samples/log.sample) | 47 | 7.0 ms | 3.1 ms | 2.1 ms | 9.0 ms | 4.9 ms | 2.9 ms |
| logo | [logo.sample](tests/Fixtures/samples/logo.sample) | 22 | 0.8 ms | 0.6 ms | 0.1 ms | 1.5 ms | 1.2 ms | 0.3 ms |
| lua | [lua.sample](tests/Fixtures/samples/lua.sample) | 204 | 23.1 ms | 18.4 ms | 4.2 ms | 32.9 ms | 28.9 ms | 6.9 ms |
| luau | [luau.sample](tests/Fixtures/samples/luau.sample) | 97 | 12.1 ms | 10.1 ms | 2.4 ms | 18.4 ms | 16.4 ms | 3.7 ms |
| make | [make.sample](tests/Fixtures/samples/make.sample) | 117 | 7.7 ms | 6.7 ms | 1.1 ms | 15.0 ms | 14.0 ms | 2.3 ms |
| maml | [maml.sample](tests/Fixtures/samples/maml.sample) | 29 | 1.4 ms | 1.2 ms | 0.2 ms | 4.4 ms | 4.3 ms | 0.3 ms |
| markdown | [markdown.sample](tests/Fixtures/samples/markdown.sample) | 449 | 131 ms | 121 ms | 17.6 ms | 182 ms | 174 ms | 11.2 ms |
| marko | [marko.sample](tests/Fixtures/samples/marko.sample) | 125 | 78.6 ms | 69.1 ms | 13.4 ms | 103 ms | 88.0 ms | 15.7 ms |
| matlab | [matlab.sample](tests/Fixtures/samples/matlab.sample) | 115 | 9.0 ms | 7.6 ms | 1.7 ms | 17.4 ms | 16.0 ms | 2.8 ms |
| mdc | [mdc.sample](tests/Fixtures/samples/mdc.sample) | 34 | 5.1 ms | 4.3 ms | 21.0 ms | 8.4 ms | 7.7 ms | 21.0 ms |
| mdx | [mdx.sample](tests/Fixtures/samples/mdx.sample) | 135 | 40.6 ms | 55.0 ms | 5.2 ms | 60.4 ms | 76.1 ms | 6.6 ms |
| mermaid | [mermaid.sample](tests/Fixtures/samples/mermaid.sample) | 224 | 10.5 ms | 6.9 ms | 3.2 ms | 16.1 ms | 12.4 ms | 5.8 ms |
| mipsasm | [mipsasm.sample](tests/Fixtures/samples/mipsasm.sample) | 25 | 1.1 ms | 0.8 ms | 0.2 ms | 2.0 ms | 1.7 ms | 0.5 ms |
| mojo | [mojo.sample](tests/Fixtures/samples/mojo.sample) | 209 | 46.6 ms | 36.3 ms | 11.3 ms | 70.9 ms | 58.9 ms | 13.5 ms |
| move | [move.sample](tests/Fixtures/samples/move.sample) | 151 | 13.0 ms | 11.0 ms | 2.7 ms | 31.5 ms | 29.9 ms | 6.8 ms |
| narrat | [narrat.sample](tests/Fixtures/samples/narrat.sample) | 92 | 4.8 ms | 3.6 ms | 1.4 ms | 7.5 ms | 6.3 ms | 2.2 ms |
| neon | [neon.sample](tests/Fixtures/samples/neon.sample) | 97 | 4.2 ms | 3.5 ms | 0.8 ms | 7.8 ms | 7.1 ms | 1.4 ms |
| nextflow | [nextflow.sample](tests/Fixtures/samples/nextflow.sample) | 138 | 1.6 ms | 1.3 ms | 0.5 ms | 3.2 ms | 2.9 ms | 1.2 ms |
| nginx | [nginx.sample](tests/Fixtures/samples/nginx.sample) | 213 | 25.0 ms | 20.6 ms | 3.1 ms | 35.8 ms | 31.5 ms | 4.6 ms |
| nim | [nim.sample](tests/Fixtures/samples/nim.sample) | 177 | 23.7 ms | 17.6 ms | 7.7 ms | 33.9 ms | 26.7 ms | 11.1 ms |
| nix | [nix.sample](tests/Fixtures/samples/nix.sample) | 45 | 4.9 ms | 4.2 ms | 0.8 ms | 6.8 ms | 6.1 ms | 1.4 ms |
| nushell | [nushell.sample](tests/Fixtures/samples/nushell.sample) | 68 | 12.9 ms | 9.9 ms | 2.7 ms | 26.4 ms | 23.1 ms | 3.3 ms |
| objective-c | [objective-c.sample](tests/Fixtures/samples/objective-c.sample) | 177 | 58.5 ms | 41.6 ms | 18.2 ms | 89.6 ms | 73.2 ms | 15.7 ms |
| objective-cpp | [objective-cpp.sample](tests/Fixtures/samples/objective-cpp.sample) | 229 | 87.4 ms | 65.2 ms | 23.8 ms | 133 ms | 114 ms | 27.3 ms |
| ocaml | [ocaml.sample](tests/Fixtures/samples/ocaml.sample) | 140 | 34.1 ms | 26.4 ms | 8.0 ms | 42.3 ms | 34.8 ms | 10.9 ms |
| pascal | [pascal.sample](tests/Fixtures/samples/pascal.sample) | 110 | 5.2 ms | 2.9 ms | 2.1 ms | 7.3 ms | 4.9 ms | 2.9 ms |
| perl | [perl.sample](tests/Fixtures/samples/perl.sample) | 170 | 27.1 ms | 22.2 ms | 6.0 ms | 42.8 ms | 38.3 ms | 8.3 ms |
| php | [php.sample](tests/Fixtures/samples/php.sample) | 465 | 193 ms | 153 ms | 43.3 ms | 259 ms | 216 ms | 50.0 ms |
| pkl | [pkl.sample](tests/Fixtures/samples/pkl.sample) | 138 | 12.5 ms | 9.9 ms | 2.5 ms | 18.1 ms | 15.5 ms | 3.8 ms |
| plsql | [plsql.sample](tests/Fixtures/samples/plsql.sample) | 121 | 14.7 ms | 8.4 ms | 7.7 ms | 19.4 ms | 13.0 ms | 9.4 ms |
| polar | [polar.sample](tests/Fixtures/samples/polar.sample) | 150 | 8.1 ms | 6.8 ms | 1.5 ms | 14.1 ms | 13.1 ms | 2.9 ms |
| postcss | [postcss.sample](tests/Fixtures/samples/postcss.sample) | 141 | 9.1 ms | 7.2 ms | 1.8 ms | 13.9 ms | 11.8 ms | 3.4 ms |
| powerquery | [powerquery.sample](tests/Fixtures/samples/powerquery.sample) | 23 | 2.4 ms | 2.0 ms | 0.5 ms | 3.9 ms | 3.5 ms | 0.8 ms |
| powershell | [powershell.sample](tests/Fixtures/samples/powershell.sample) | 146 | 20.6 ms | 15.7 ms | 5.5 ms | 40.0 ms | 35.4 ms | 7.4 ms |
| prisma | [prisma.sample](tests/Fixtures/samples/prisma.sample) | 42 | 1.5 ms | 1.3 ms | 0.2 ms | 4.0 ms | 3.8 ms | 0.6 ms |
| prolog | [prolog.sample](tests/Fixtures/samples/prolog.sample) | 180 | 19.9 ms | 17.1 ms | 3.9 ms | 29.7 ms | 26.0 ms | 6.2 ms |
| proto | [proto.sample](tests/Fixtures/samples/proto.sample) | 45 | 1.6 ms | 1.4 ms | 0.2 ms | 3.3 ms | 3.0 ms | 0.7 ms |
| pug | [pug.sample](tests/Fixtures/samples/pug.sample) | 87 | 24.6 ms | 21.9 ms | 59.0 ms | 37.1 ms | 33.5 ms | 61.1 ms |
| puppet | [puppet.sample](tests/Fixtures/samples/puppet.sample) | 138 | 10.0 ms | 8.0 ms | 1.8 ms | 18.7 ms | 16.9 ms | 3.2 ms |
| purescript | [purescript.sample](tests/Fixtures/samples/purescript.sample) | 82 | 9.7 ms | 8.0 ms | 2.0 ms | 15.6 ms | 14.5 ms | 3.2 ms |
| python | [python.sample](tests/Fixtures/samples/python.sample) | 348 | 77.8 ms | 88.3 ms | 21.3 ms | 121 ms | 107 ms | 28.9 ms |
| qml | [qml.sample](tests/Fixtures/samples/qml.sample) | 27 | 7.9 ms | 7.0 ms | 1.4 ms | 11.0 ms | 10.5 ms | 1.7 ms |
| qmldir | [qmldir.sample](tests/Fixtures/samples/qmldir.sample) | 8 | 0.2 ms | 0.2 ms | 0.0 ms | 0.6 ms | 0.6 ms | 0.1 ms |
| qss | [qss.sample](tests/Fixtures/samples/qss.sample) | 37 | 1.7 ms | 1.3 ms | 0.3 ms | 3.0 ms | 2.5 ms | 0.7 ms |
| r | [r.sample](tests/Fixtures/samples/r.sample) | 106 | 44.8 ms | 25.1 ms | 9.3 ms | 62.9 ms | 37.8 ms | 11.3 ms |
| racket | [racket.sample](tests/Fixtures/samples/racket.sample) | 127 | 174 ms | 145 ms | 9.9 ms | 184 ms | 156 ms | 11.6 ms |
| raku | [raku.sample](tests/Fixtures/samples/raku.sample) | 134 | 11.0 ms | 7.2 ms | 2.6 ms | 15.3 ms | 11.2 ms | 4.2 ms |
| razor | [razor.sample](tests/Fixtures/samples/razor.sample) | 138 | 58.4 ms | 78.6 ms | 20.7 ms | 123 ms | 117 ms | 22.3 ms |
| reg | [reg.sample](tests/Fixtures/samples/reg.sample) | 27 | 0.6 ms | 0.5 ms | 0.1 ms | 1.4 ms | 1.3 ms | 0.3 ms |
| regexp | [regexp.sample](tests/Fixtures/samples/regexp.sample) | 30 | 4.4 ms | 3.9 ms | 0.9 ms | 8.5 ms | 8.0 ms | 1.5 ms |
| rel | [rel.sample](tests/Fixtures/samples/rel.sample) | 66 | 2.8 ms | 1.8 ms | 0.9 ms | 4.3 ms | 3.4 ms | 1.6 ms |
| riscv | [riscv.sample](tests/Fixtures/samples/riscv.sample) | 31 | 2.4 ms | 1.8 ms | 0.4 ms | 3.7 ms | 3.0 ms | 0.6 ms |
| rosmsg | [rosmsg.sample](tests/Fixtures/samples/rosmsg.sample) | 67 | 4.1 ms | 3.5 ms | 0.7 ms | 6.6 ms | 6.0 ms | 1.2 ms |
| rst | [rst.sample](tests/Fixtures/samples/rst.sample) | 149 | 11.8 ms | 10.5 ms | 2.1 ms | 17.4 ms | 15.5 ms | 2.2 ms |
| ruby | [ruby.sample](tests/Fixtures/samples/ruby.sample) | 329 | 92.5 ms | 106 ms | 23.1 ms | 126 ms | 113 ms | 28.0 ms |
| rust | [rust.sample](tests/Fixtures/samples/rust.sample) | 154 | 26.1 ms | 22.8 ms | 6.3 ms | 37.9 ms | 33.4 ms | 8.5 ms |
| sas | [sas.sample](tests/Fixtures/samples/sas.sample) | 75 | 10.1 ms | 5.3 ms | 3.6 ms | 14.2 ms | 9.2 ms | 4.9 ms |
| sass | [sass.sample](tests/Fixtures/samples/sass.sample) | 104 | 9.9 ms | 7.8 ms | 1.9 ms | 16.0 ms | 13.8 ms | 2.9 ms |
| scala | [scala.sample](tests/Fixtures/samples/scala.sample) | 218 | 49.5 ms | 39.8 ms | 9.9 ms | 66.2 ms | 56.0 ms | 14.4 ms |
| scheme | [scheme.sample](tests/Fixtures/samples/scheme.sample) | 27 | 1.7 ms | 1.3 ms | 0.3 ms | 3.8 ms | 3.4 ms | 0.6 ms |
| scss | [scss.sample](tests/Fixtures/samples/scss.sample) | 229 | 64.5 ms | 34.2 ms | 22.1 ms | 96.4 ms | 65.6 ms | 25.9 ms |
| sdbl | [sdbl.sample](tests/Fixtures/samples/sdbl.sample) | 22 | 1.2 ms | 0.7 ms | 0.7 ms | 1.9 ms | 1.3 ms | 0.9 ms |
| shaderlab | [shaderlab.sample](tests/Fixtures/samples/shaderlab.sample) | 61 | 4.1 ms | 2.8 ms | 1.3 ms | 5.7 ms | 4.5 ms | 1.7 ms |
| shellscript | [shellscript.sample](tests/Fixtures/samples/shellscript.sample) | 200 | 40.5 ms | 32.3 ms | 9.5 ms | 77.9 ms | 72.0 ms | 12.2 ms |
| shellsession | [shellsession.sample](tests/Fixtures/samples/shellsession.sample) | 14 | 1.0 ms | 0.6 ms | 0.1 ms | 1.7 ms | 1.3 ms | 0.6 ms |
| smalltalk | [smalltalk.sample](tests/Fixtures/samples/smalltalk.sample) | 12 | 1.1 ms | 1.0 ms | 0.2 ms | 3.0 ms | 2.9 ms | 0.4 ms |
| solidity | [solidity.sample](tests/Fixtures/samples/solidity.sample) | 173 | 27.2 ms | 24.1 ms | 7.3 ms | 35.6 ms | 33.2 ms | 9.3 ms |
| soy | [soy.sample](tests/Fixtures/samples/soy.sample) | 89 | 13.0 ms | 10.2 ms | 2.1 ms | 19.5 ms | 17.1 ms | 3.1 ms |
| sparql | [sparql.sample](tests/Fixtures/samples/sparql.sample) | 8 | 0.5 ms | 0.4 ms | 0.1 ms | 1.0 ms | 0.9 ms | 0.2 ms |
| splunk | [splunk.sample](tests/Fixtures/samples/splunk.sample) | 16 | 1.5 ms | 1.0 ms | 0.5 ms | 2.4 ms | 2.0 ms | 0.9 ms |
| sql | [sql.sample](tests/Fixtures/samples/sql.sample) | 135 | 32.5 ms | 18.8 ms | 12.7 ms | 39.5 ms | 21.8 ms | 15.1 ms |
| ssh-config | [ssh-config.sample](tests/Fixtures/samples/ssh-config.sample) | 48 | 1.7 ms | 1.3 ms | 0.3 ms | 2.7 ms | 2.2 ms | 0.7 ms |
| stata | [stata.sample](tests/Fixtures/samples/stata.sample) | 143 | 37.7 ms | 27.3 ms | 7.6 ms | 46.8 ms | 34.3 ms | 9.7 ms |
| stylus | [stylus.sample](tests/Fixtures/samples/stylus.sample) | 133 | 20.5 ms | 14.9 ms | 3.9 ms | 33.8 ms | 28.3 ms | 5.3 ms |
| svelte | [svelte.sample](tests/Fixtures/samples/svelte.sample) | 137 | 238 ms | 284 ms | 20.1 ms | 327 ms | 323 ms | 23.7 ms |
| swift | [swift.sample](tests/Fixtures/samples/swift.sample) | 502 | 198 ms | 180 ms | 36.5 ms | 263 ms | 241 ms | 42.8 ms |
| system-verilog | [system-verilog.sample](tests/Fixtures/samples/system-verilog.sample) | 184 | 33.8 ms | 27.0 ms | 10.6 ms | 46.1 ms | 40.1 ms | 19.4 ms |
| systemd | [systemd.sample](tests/Fixtures/samples/systemd.sample) | 22 | 0.7 ms | 0.5 ms | 0.1 ms | 1.2 ms | 1.1 ms | 0.3 ms |
| talonscript | [talonscript.sample](tests/Fixtures/samples/talonscript.sample) | 120 | 6.1 ms | 5.1 ms | 1.1 ms | 10.1 ms | 9.2 ms | 2.4 ms |
| tasl | [tasl.sample](tests/Fixtures/samples/tasl.sample) | 26 | 1.0 ms | 0.8 ms | 0.1 ms | 2.1 ms | 1.9 ms | 0.4 ms |
| tcl | [tcl.sample](tests/Fixtures/samples/tcl.sample) | 162 | 6.4 ms | 4.9 ms | 1.3 ms | 10.9 ms | 9.4 ms | 3.1 ms |
| templ | [templ.sample](tests/Fixtures/samples/templ.sample) | 113 | 15.5 ms | 52.0 ms | 4.7 ms | 35.6 ms | 79.3 ms | 5.2 ms |
| terraform | [terraform.sample](tests/Fixtures/samples/terraform.sample) | 162 | 16.0 ms | 13.5 ms | 2.9 ms | 29.9 ms | 26.9 ms | 4.7 ms |
| tex | [tex.sample](tests/Fixtures/samples/tex.sample) | 30 | 1.5 ms | 1.1 ms | 0.3 ms | 3.1 ms | 2.6 ms | 0.5 ms |
| toml | [toml.sample](tests/Fixtures/samples/toml.sample) | 26 | 1.5 ms | 1.4 ms | 0.2 ms | 3.5 ms | 3.2 ms | 0.4 ms |
| ts-tags | [ts-tags.sample](tests/Fixtures/samples/ts-tags.sample) | 50 | 8.0 ms | 6.9 ms | 0.9 ms | 11.9 ms | 11.2 ms | 5.8 ms |
| tsv | [tsv.sample](tests/Fixtures/samples/tsv.sample) | 6 | 0.2 ms | 0.1 ms | 0.0 ms | 0.5 ms | 0.5 ms | 0.1 ms |
| tsx | [tsx.sample](tests/Fixtures/samples/tsx.sample) | 518 | 257 ms | 245 ms | 40.4 ms | 387 ms | 381 ms | 52.7 ms |
| turtle | [turtle.sample](tests/Fixtures/samples/turtle.sample) | 16 | 0.8 ms | 0.7 ms | 0.1 ms | 1.8 ms | 1.6 ms | 0.4 ms |
| twig | [twig.sample](tests/Fixtures/samples/twig.sample) | 120 | 24.8 ms | 21.0 ms | 4.9 ms | 44.7 ms | 39.0 ms | 7.4 ms |
| txt | [txt.sample](tests/Fixtures/samples/txt.sample) | 33 | 0.1 ms | 0.1 ms | 0.3 ms | 0.3 ms | 0.3 ms | 0.5 ms |
| typescript | [typescript.sample](tests/Fixtures/samples/typescript.sample) | 530 | 281 ms | 240 ms | 40.3 ms | 353 ms | 341 ms | 134 ms |
| typespec | [typespec.sample](tests/Fixtures/samples/typespec.sample) | 36 | 3.5 ms | 3.0 ms | 0.5 ms | 8.6 ms | 8.1 ms | 0.9 ms |
| typst | [typst.sample](tests/Fixtures/samples/typst.sample) | 28 | 3.6 ms | 2.7 ms | 0.5 ms | 5.9 ms | 5.1 ms | 0.9 ms |
| v | [v.sample](tests/Fixtures/samples/v.sample) | 203 | 25.0 ms | 20.3 ms | 5.2 ms | 32.6 ms | 28.7 ms | 7.2 ms |
| vala | [vala.sample](tests/Fixtures/samples/vala.sample) | 171 | 8.6 ms | 6.5 ms | 1.8 ms | 13.5 ms | 11.4 ms | 3.6 ms |
| vb | [vb.sample](tests/Fixtures/samples/vb.sample) | 39 | 4.1 ms | 2.9 ms | 1.2 ms | 6.4 ms | 5.2 ms | 1.5 ms |
| verilog | [verilog.sample](tests/Fixtures/samples/verilog.sample) | 31 | 1.4 ms | 1.1 ms | 0.2 ms | 2.7 ms | 2.4 ms | 0.5 ms |
| vhdl | [vhdl.sample](tests/Fixtures/samples/vhdl.sample) | 216 | 29.0 ms | 22.0 ms | 47.6 ms | 43.9 ms | 31.5 ms | 48.8 ms |
| viml | [viml.sample](tests/Fixtures/samples/viml.sample) | 126 | 18.1 ms | 10.6 ms | 4.9 ms | 23.1 ms | 15.4 ms | 6.2 ms |
| vue | [vue.sample](tests/Fixtures/samples/vue.sample) | 188 | 79.1 ms | 41.5 ms | 10.3 ms | 126 ms | 116 ms | 21.7 ms |
| vue-html | [vue-html.sample](tests/Fixtures/samples/vue-html.sample) | 71 | 19.7 ms | 17.4 ms | 4.3 ms | 30.8 ms | 32.3 ms | 6.1 ms |
| vue-vine | [vue-vine.sample](tests/Fixtures/samples/vue-vine.sample) | 253 | 119 ms | 109 ms | 20.9 ms | 209 ms | 190 ms | 34.0 ms |
| vyper | [vyper.sample](tests/Fixtures/samples/vyper.sample) | 192 | 43.5 ms | 34.0 ms | 10.6 ms | 56.1 ms | 46.8 ms | 12.7 ms |
| wasm | [wasm.sample](tests/Fixtures/samples/wasm.sample) | 175 | 27.0 ms | 20.9 ms | 5.6 ms | 34.0 ms | 29.3 ms | 8.6 ms |
| wgsl | [wgsl.sample](tests/Fixtures/samples/wgsl.sample) | 19 | 1.9 ms | 1.6 ms | 0.4 ms | 3.4 ms | 3.1 ms | 0.6 ms |
| wikitext | [wikitext.sample](tests/Fixtures/samples/wikitext.sample) | 33 | 10.1 ms | 8.2 ms | 3.1 ms | 13.7 ms | 11.2 ms | 3.9 ms |
| wit | [wit.sample](tests/Fixtures/samples/wit.sample) | 59 | 5.9 ms | 4.2 ms | 0.7 ms | 16.6 ms | 16.3 ms | 1.2 ms |
| wolfram | [wolfram.sample](tests/Fixtures/samples/wolfram.sample) | 176 | 302 ms | 252 ms | 65.7 ms | 335 ms | 270 ms | 70.3 ms |
| xml | [xml.sample](tests/Fixtures/samples/xml.sample) | 123 | 6.4 ms | 5.3 ms | 1.1 ms | 10.3 ms | 9.3 ms | 2.6 ms |
| xsl | [xsl.sample](tests/Fixtures/samples/xsl.sample) | 66 | 3.0 ms | 2.6 ms | 0.5 ms | 5.9 ms | 5.5 ms | 1.1 ms |
| yaml | [yaml.sample](tests/Fixtures/samples/yaml.sample) | 87 | 10.0 ms | 7.3 ms | 1.7 ms | 17.7 ms | 14.5 ms | 2.6 ms |
| zenscript | [zenscript.sample](tests/Fixtures/samples/zenscript.sample) | 44 | 2.1 ms | 1.6 ms | 0.5 ms | 3.8 ms | 3.3 ms | 1.0 ms |
| zig | [zig.sample](tests/Fixtures/samples/zig.sample) | 26 | 2.9 ms | 2.5 ms | 0.6 ms | 4.3 ms | 3.9 ms | 1.1 ms |
