<?php

namespace Phiki;

/**
 * This class is responsible for converting an Oniguruma pattern into a PCRE2/PHP compatible pattern.
 */
class Regex
{
    const SLASH_P_MAP = [
        'alnum' => '0-9A-Za-z',
        'alpha' => 'A-Za-z',
        'alphabetic' => 'A-Za-z',
        'blank' => '\\s',
        'greek' => '\\p{Greek}',
        'print' => '\\p{L}\\p{N}\\p{P}\\p{S}\\p{Zs}',
        'word' => '\\w',
    ];

    public function __construct(
        protected string $pattern,
        protected ?string $lowered = null,
    ) {}

    public function get(): string
    {
        if ($this->lowered !== null) {
            return $this->lowered;
        }

        $pattern = preg_replace('/(?<!\\\)\//', '\\/', $this->pattern);
        $pattern = $this->convertEscapeSequences($pattern);
        $pattern = $this->convertUnicodeProperties($pattern);
        $pattern = $this->escapeInvalidLeadingRangeCharacter($pattern);
        $pattern = $this->escapeUnescapedCloseSetCharacters($pattern);

        return $this->lowered = $pattern;
    }

    protected function convertEscapeSequences(string $pattern): string
    {
        // Convert \h to [0-9A-Fa-f].
        $pattern = preg_replace('/\\\\h/', '[0-9A-Fa-f]', $pattern);

        // Convert \H to [^0-9A-Fa-f].
        $pattern = preg_replace('/\\\\H/', '[^0-9A-Fa-f]', $pattern);

        return $pattern;
    }

    protected function convertUnicodeProperties(string $pattern): string
    {
        // Convert \p{xx} to PCRE-compatible \p{xx}.
        $pattern = preg_replace_callback('/\\\p\{([a-zA-Z]+)\}/', function (array $matches) {
            $property = strtolower($matches[1]);

            if (isset(self::SLASH_P_MAP[$property])) {
                return '[' . self::SLASH_P_MAP[$property] . ']';
            }

            return $matches[0];
        }, $pattern);

        return $pattern;
    }

    protected function escapeInvalidLeadingRangeCharacter(string $pattern): string
    {
        // Escape invalid leading range function characters, e.g. [-...].
        $pattern = preg_replace('/\[(?<!\\\)(-)/', '[\\-', $pattern);

        return $pattern;
    }

    protected function escapeUnescapedCloseSetCharacters(string $pattern): string
    {
        // Escape unescaped close set characters, e.g. ]] converted to \]].
        $pattern = preg_replace('/(?<!\\\)\]\]/', '\\]]', $pattern);

        return $pattern;
    }
}
