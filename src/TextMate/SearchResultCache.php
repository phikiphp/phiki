<?php

namespace Phiki\TextMate;

/**
 * Remembers where each regex last matched in the current line, so a later search
 * further along the same line can reuse the result instead of scanning again.
 *
 * A match found at position M by searching from position S is also the first match
 * from any position between S and M, and a regex with no match from S has none
 * further along either. The one exception is `\G`, which anchors to wherever the
 * search starts, so regexes containing it are never cached.
 */
class SearchResultCache
{
    /**
     * The line the cached results belong to.
     */
    protected ?string $line = null;

    /**
     * The position each regex was searched from, where it matched, or false, and what the engine stored with it.
     *
     * @var array<string, array{ 0: int, 1: int|false, 2: mixed }>
     */
    protected array $results = [];

    /**
     * Start searching the given line, forgetting results from any other line.
     *
     * Returns false if this is already the current line, so an engine that keeps the line
     * it searches doesn't need to be given it again.
     */
    public function useLine(string $line): bool
    {
        if ($line === $this->line) {
            return false;
        }

        $this->line = $line;
        $this->results = [];

        return true;
    }

    /**
     * Get the result of searching for the regex from the given position: false if it has no match,
     * the position it matched at and what the engine stored with it, or null if that isn't known.
     *
     * @return array{ 0: int, 1: mixed }|false|null
     */
    public function get(string $regex, int $position): array|false|null
    {
        if (! isset($this->results[$regex])) {
            return null;
        }

        [$from, $start, $match] = $this->results[$regex];

        if ($position < $from || ($start !== false && $start < $position)) {
            return null;
        }

        return $start === false ? false : [$start, $match];
    }

    /**
     * Remember the result of searching for the regex from the given position.
     */
    public function put(string $regex, int $position, int|false $start, mixed $match = null): void
    {
        if (str_contains($regex, '\G')) {
            return;
        }

        $this->results[$regex] = [$position, $start, $match];
    }
}
