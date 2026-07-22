<?php

namespace Phiki\Support;

final class Styles
{
    /**
     * Join a set of style chunks into a single, well-formed `style` attribute value.
     *
     * Chunks may arrive with or without a trailing separator, and may be empty or
     * null, so we normalise them into individual declarations before joining. The
     * result always ends with a separator, which means anything appending to the
     * attribute later can safely concatenate without gluing itself onto the last
     * declaration.
     *
     * @param  array<int, string|null>  $chunks
     */
    public static function join(array $chunks): string
    {
        $declarations = [];

        foreach ($chunks as $chunk) {
            foreach (explode(';', $chunk ?? '') as $declaration) {
                $declaration = trim($declaration);

                if ($declaration === '') {
                    continue;
                }

                $declarations[] = $declaration;
            }
        }

        if ($declarations === []) {
            return '';
        }

        return implode(';', $declarations).';';
    }
}
