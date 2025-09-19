#!/usr/bin/env bash
set -euo pipefail

GRAMMARS_DIR="$(realpath $(dirname "$0")/../resources/grammars)"

echo "Minifying grammars in $GRAMMARS_DIR"

for grammar in "$GRAMMARS_DIR"/*.json; do
    jq -c . "$grammar" > "$grammar.tmp" && mv "$grammar.tmp" "$grammar"
done

THEMES_DIR="$(realpath $(dirname "$0")/../resources/themes)"

echo "Minifying themes in $THEMES_DIR"

for theme in "$THEMES_DIR"/*.json; do
    jq -c . "$theme" > "$theme.tmp" && mv "$theme.tmp" "$theme"
done
