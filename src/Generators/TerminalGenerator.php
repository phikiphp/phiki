<?php

namespace Phiki\Generators;

use Phiki\Contracts\OutputGeneratorInterface;
use Phiki\Support\Color;
use Phiki\Theme\ParsedTheme;

class TerminalGenerator implements OutputGeneratorInterface
{
    public function __construct(
        protected ParsedTheme $theme,
    ) {}

    public function generate(array $tokens): string
    {
        $output = '';

        foreach ($tokens as $line) {
            foreach ($line as $token) {
                if ($token->settings !== []) {
                    $output .= $token->settings['default']->toAnsiEscape();
                }

                $output .= $token->token->text;

                if ($token->settings !== []) {
                    $output .= Color::ANSI_RESET;
                }
            }
        }

        return $output;
    }
}
