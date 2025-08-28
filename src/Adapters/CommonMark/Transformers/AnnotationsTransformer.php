<?php

namespace Phiki\Adapters\CommonMark\Transformers;

use Phiki\Adapters\CommonMark\Transformers\Annotations\Annotation;
use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationRange;
use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationRangeKind;
use Phiki\Adapters\CommonMark\Transformers\Annotations\AnnotationType;
use Phiki\Contracts\RequiresGrammarInterface;
use Phiki\Contracts\RequiresThemesInterface;
use Phiki\Grammar\Grammar;
use Phiki\Phast\Element;
use Phiki\Support\Arr;
use Phiki\Transformers\AbstractTransformer;
use Phiki\Transformers\Concerns\RequiresGrammar;
use Phiki\Transformers\Concerns\RequiresThemes;

class AnnotationsTransformer extends AbstractTransformer implements RequiresGrammarInterface, RequiresThemesInterface
{
    use RequiresGrammar;
    use RequiresThemes;

    const ANNOTATION_REGEX = '/\[%s! (?<keyword>%s)(:(?<range>.+))?\]/';

    const DANGLING_LINE_COMMENT_REGEX = '/(%s)\s*$/';

    const COMMON_COMMENT_CHARACTERS = [
        '#', '//', ['/*', '*/'], ['/**', '*/'],
    ];

    const GRAMMAR_SPECIFIC_COMMENT_CHARACTERS = [
        Grammar::Antlers->value => ['{{#', '#}}'],
        Grammar::Blade->value => ['{{--', '--}}'],
        Grammar::Coq->value => ['(*', '*)'],
        Grammar::Asm->value => ';',
        Grammar::Html->value => ['<!--', '-->'],
        Grammar::Xml->value => ['<!--', '-->'],
        Grammar::Ini->value => [';'],
    ];

    /**
     * The collected list of annotations.
     * 
     * @var array<int, array<Annotation>>
     */
    protected array $annotations = [];

    /**
     * Create a new instance.
     * 
     * @param string $prefix The prefix used to denote annotations, e.g. `code` for `[code! highlight]`.
     */
    public function __construct(protected string $prefix = 'code') {}

    /**
     * Preprocess the code block content to discover annotations.
     */
    public function preprocess(string $code): string
    {
        $lines = preg_split('/\R/', $code);
        $annotations = [];
        $unclosedAnnotationsStack = [];
        $processedAnnotationRegex = sprintf(self::ANNOTATION_REGEX, preg_quote($this->prefix, '/'), implode('|', array_map(fn (string $keyword) => preg_quote($keyword, '/'), array_merge(...array_map(fn (AnnotationType $type) => $type->keywords(), AnnotationType::cases())))));

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (preg_match($processedAnnotationRegex, $line, $matches, PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE) === 0) {
                continue;
            }

            $type = AnnotationType::fromKeyword($matches['keyword'][0]);
            $annotation = null;
            $unclosed = false;

            // If there is no specified range, then it only needs to apply to the current line.
            if ($matches['range'][0] === null) {
                $annotation = new Annotation($type, $i, $i);
            } else {
                $range = AnnotationRange::parse($matches['range'][0], $i);

                // Invalid range provided, skip and move on.
                if (! $range) {
                    continue;
                }

                $unclosed = $range->kind === AnnotationRangeKind::OpenEnded;

                // If the range is open ended, then we can add it to the stack to be closed later.
                if ($unclosed) {
                    $unclosedAnnotationsStack[] = $annotation = new Annotation($type, $i, $i);
                } elseif ($range->kind === AnnotationRangeKind::End) {
                    // If the range is ending something, then we need to find the most recent unclosed annotation of the same type and close it.
                    for ($j = count($unclosedAnnotationsStack) - 1; $j >= 0; $j--) {
                        if ($unclosedAnnotationsStack[$j]->type === $type) {
                            $annotation = array_splice($unclosedAnnotationsStack, $j, 1)[0];
                            $annotation->end = $i;
                            break;
                        }
                    }
                } else {
                    // Otherwise, we have a closed range so we can construct the annotation directly.
                    $annotation = new Annotation($type, $range->start, $range->end);
                }
            }

            // We should now try to remove the annotation from the line.
            // We'll first create a clone of the line to work with, removing any trailing whitespace
            // and replacing the annotation itself.
            $trimmed = rtrim(str_replace($matches[0][0], '', $line));

            // We'll also create a variable to store the point as which we should cut off the line.
            $cutoffPoint = strlen($trimmed);

            // Some grammars have their own comment characters, e.g. Blade, Antlers, Coq, etc.
            // We'll add those to the list of characters to check.
            $commentChars = array_merge(self::COMMON_COMMENT_CHARACTERS, isset(self::GRAMMAR_SPECIFIC_COMMENT_CHARACTERS[$this->grammar->name]) ? [self::GRAMMAR_SPECIFIC_COMMENT_CHARACTERS[$this->grammar->name]] : []);

            // Then we can check for common comment characters at the end of the line.
            // We store a list of these in a constant:
            //    - strings are characters for line comments
            //    - arrays are beginning and ending comment pairs (block comments)
            [$l, $b] = Arr::partition($commentChars, fn(string|array $chars) => is_string($chars));

            // We'll first check for line comments.
            $processedLineCommentRegex = sprintf(self::DANGLING_LINE_COMMENT_REGEX, implode('|', array_map(fn(string $char) => preg_quote($char, '/'), $l)));

            // If we find a match, we can set the cutoff point and skip checking for block comments.
            if (preg_match($processedLineCommentRegex, $trimmed, $lineCommentMatches, PREG_OFFSET_CAPTURE) === 1) {
                $cutoffPoint = $lineCommentMatches[1][1];
                goto cutoff;
            }

            $processedBlockCommentRegex = sprintf(
                '/%s$/',
                implode('|', array_map(fn(array $chars) => sprintf('(%s\s*%s)', preg_quote($chars[0], '/'), preg_quote($chars[1], '/')), $b)),
            );

            // If we find a match, we can set the cutoff point.
            if (preg_match($processedBlockCommentRegex, $trimmed, $blockCommentMatches, PREG_OFFSET_CAPTURE) === 1) {
                $cutoffPoint = $blockCommentMatches[0][1];
                goto cutoff;
            }

            // If we reach here, then we didn't find any comment characters, so we'll just cut off at the annotation itself.
            $cutoffPoint = $matches[0][1];

            cutoff:
            // We can then trim the line down up to the cutoff point.
            $trimmed = substr($trimmed, 0, $cutoffPoint);

            // If the line is now completely empty, we can remove the line entirely.
            if (trim($trimmed) === '') {
                // Doing an `unset` here will leave a gap in the array, so we need to make sure we reindex too,
                // since we want future index references to point to the correct lines still.
                unset($lines[$i]);
                $lines = array_values($lines);

                // Reindexing will shift all future lines down by one, so we need to decrement $i to account for that.
                $i--;
            } else {
                // Otherwise we can just replace the line with the trimmed version.
                $lines[$i] = $trimmed;
            }

            // If the annotation is unclosed, we don't want to add it to the annotations list yet.
            if ($unclosed) {
                continue;
            }

            // We can finally add the annotation to the correct place.
            for ($k = $annotation->start; $k <= $annotation->end; $k++) {
                $annotations[$k][] = $annotation;
            }
        }

        // Any annotations left in the unclosed stack are still unclosed and should be closed at the end of the document.
        foreach ($unclosedAnnotationsStack as $unclosedAnnotation) {
            $unclosedAnnotation->end = count($lines) - 1;
            for ($k = $unclosedAnnotation->start; $k <= $unclosedAnnotation->end; $k++) {
                $annotations[$k][] = $unclosedAnnotation;
            }
        }

        $this->annotations = $annotations;

        return implode("\n", $lines);
    }

    public function pre(Element $pre): Element
    {
        if ($this->annotations === []) {
            return $pre;
        }

        foreach ($this->annotations as $annotations) {
            foreach ($annotations as $annotation) {
                $annotation->applyToPre($pre);
            }
        }

        return $pre;
    }

    public function line(Element $span, array $tokens, int $index): Element
    {
        if ($this->annotations === [] || ! isset($this->annotations[$index])) {
            return $span;
        }

        foreach ($this->annotations[$index] as $annotation) {
            $annotation->applyToLine($span);
        }

        return $span;
    }
}
