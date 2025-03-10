<?php

namespace Phiki;

use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\Contracts\ProvidesContentName;
use Phiki\Environment\Environment;
use Phiki\Exceptions\IndeterminateStateException;
use Phiki\Exceptions\UnreachableException;
use Phiki\Exceptions\UnrecognisedGrammarException;
use Phiki\Grammar\BeginEndPattern;
use Phiki\Grammar\BeginWhilePattern;
use Phiki\Grammar\CollectionPattern;
use Phiki\Grammar\EndPattern;
use Phiki\Grammar\IncludePattern;
use Phiki\Grammar\Injections\Prefix;
use Phiki\Grammar\MatchedInjection;
use Phiki\Grammar\MatchedPattern;
use Phiki\Grammar\MatchPattern;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Grammar\Pattern;
use Phiki\Grammar\WhilePattern;
use Phiki\Token\Token;

class Tokenizer
{
    protected State $state;

    protected array $tokens = [];

    public function __construct(
        protected ParsedGrammar $grammar,
        protected Environment $environment,
    ) {}

    public function tokenize(string $input): array
    {
        $this->state = new State;
        $this->state->pushPattern($this->grammar);
        $this->state->pushScopes(preg_split('/\s+/', $this->grammar->scopeName));

        $this->tokens = [];

        $lines = preg_split("/\R/", $input);

        foreach ($lines as $line => $lineText) {
            if ($line > 0) {
                $this->state->setNotFirstLine();
            }

            $this->state->setLinePosition(0);
            $this->state->resetAnchorPositions();

            $this->tokenizeLine($line, $lineText."\n");
        }

        return $this->tokens;
    }

    public function tokenizeLine(int $line, string $lineText): void
    {
        $this->checkWhileConditions($line, $lineText);

        while ($this->state->getLinePosition() < strlen($lineText)) {
            $remainingText = substr($lineText, $this->state->getLinePosition());
            $root = $this->state->getPattern();
            $matched = $this->match($lineText);
            $endIsMatched = false;

            // FIXME: Move all of this end pattern checking into the `match` method!
            // Some patterns will include `$self`. Since we're not fixing all patterns to match at the end of the previous match
            // we need to check if we're looking for an `end` pattern that is closer than the matched subpattern.
            if ($matched !== false && $root instanceof EndPattern && $endMatched = $root->tryMatch($this, $lineText, $this->state->getLinePosition())) {
                if ($endMatched->offset() <= $matched->offset()) {
                    $matched = $endMatched;
                    $endIsMatched = true;
                }
            }

            // We didn't find a matching subpattern and we're looking for an `end` pattern.
            // If we find it on this line, we need to pop it off the stack and process the end pattern.
            if ($matched === false && $root instanceof EndPattern && $matched = $root->tryMatch($this, $lineText, $this->state->getLinePosition())) {
                $endIsMatched = true;
            }

            // No match found, advance to the end of the line.
            if ($matched === false) {
                $this->tokens[$line][] = new Token(
                    $this->state->getScopes(),
                    substr($lineText, $this->state->getLinePosition()),
                    $this->state->getLinePosition(),
                    strlen($lineText) - 1,
                );

                break;
            }

            if ($matched->pattern->wasInjected()) {
                $this->state->setActiveInjection();
            }

            // We've found a match for an `end` here. We need to remove it from the stack.
            // It's important that we do this here since we don't want to have an effect
            // on any capture patterns etc.
            if ($endIsMatched) {
                $poppedPattern = $this->state->popPattern();

                if ($poppedPattern->wasInjected()) {
                    $this->state->resetActiveInjection();
                }

                if ($root instanceof ProvidesContentName && $root->getContentName() !== null) {
                    $this->state->popScope();
                }
            }

            // Match found – process pattern rules and continue.
            $this->process($matched, $line, $lineText);

            if ($endIsMatched) {
                $this->state->setAnchorPosition($this->state->popAnchorPosition());
            }

            if ($endIsMatched && $root->scope() && count($this->state->getScopes()) > 1) {
                foreach ($root->scope() as $_) {
                    $this->state->popScope();
                }
            }
        }
    }

    public function matchUsing(string $lineText, array $patterns): MatchedPattern|false
    {
        $patternStack = $this->state->getPatterns();

        $this->state->setPatterns([
            new CollectionPattern($patterns),
        ]);

        $matched = $this->matchRule($lineText);

        $this->state->setPatterns($patternStack);

        return $matched;
    }

    public function match(string $lineText): MatchedPattern|false
    {
        $ruleMatch = $this->matchRule($lineText);
        $injectionMatch = $this->matchInjections($lineText);

        // No injections matched, can return early.
        if ($injectionMatch === false) {
            return $ruleMatch;
        }

        // No rules matched but an injection was matched, can return early.
        if ($ruleMatch === false) {
            return $injectionMatch->matchedPattern;
        }

        // If the injection is closer than the rule, the injection wins.
        if ($injectionMatch->offset() < $ruleMatch->offset()) {
            return $injectionMatch->matchedPattern;
        }

        // If the injection has a `L:` prefix (indicating a high priority) and the offset is the same, the injection wins.
        if ($injectionMatch->injection->getPrefix($this->state->getScopes()) === Prefix::Left && $injectionMatch->offset() === $ruleMatch->offset()) {
            return $injectionMatch->matchedPattern;
        }

        return $ruleMatch;
    }

    public function matchRule(string $lineText): MatchedPattern|false
    {
        $root = $this->state->getPattern();

        if (! $root instanceof PatternCollectionInterface) {
            throw new IndeterminateStateException('Root patterns must contain child patterns and implement '.PatternCollectionInterface::class);
        }

        $closest = false;
        $offset = $this->state->getLinePosition();
        $patterns = $root->getPatterns();

        foreach ($patterns as $pattern) {
            $matched = $pattern->tryMatch($this, $lineText, $this->state->getLinePosition());

            // No match found. Move on to next pattern.
            if ($matched === false) {
                continue;
            }

            // Match found and is same as current position. Return it.
            if ($matched->offset() === $this->state->getLinePosition()) {
                return $matched;
            }

            // First match found. Set it as the closest one.
            if ($closest === false) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }

            // Match found, closer than previous one.
            if ($matched->offset() < $offset) {
                $closest = $matched;
                $offset = $matched->offset();

                continue;
            }
        }

        return $closest;
    }

    public function matchInjections(string $lineText): MatchedInjection|false
    {
        if ($this->state->hasActiveInjection() || ! $this->grammar->hasInjections()) {
            return false;
        }

        $offset = PHP_INT_MAX;
        $matchedInjection = false;
        $scopes = $this->state->getScopes();

        foreach ($this->grammar->getInjections() as $injection) {
            if (! $injection->matches($scopes)) {
                continue;
            }

            $matched = $injection->pattern->tryMatch($this, $lineText, $this->state->getLinePosition());

            if ($matched === false) {
                continue;
            }

            if ($matched->offset() >= $offset) {
                continue;
            }

            $offset = $matched->offset();
            $matchedInjection = new MatchedInjection($injection, $matched);

            if ($offset === $this->state->getLinePosition()) {
                break;
            }
        }

        return $matchedInjection;
    }

    public function resolve(IncludePattern $pattern): ?Pattern
    {
        $repository = $this->environment->getGrammarRepository();

        try {
            return match (true) {
                // "include": "$self"
                $pattern->isSelf() => $repository->getFromScope($pattern->getScopeName() ?? $this->grammar->scopeName),
                // "include": "$base"
                $pattern->isBase() => $this->grammar,
                // "include": "#name"
                $pattern->getReference() && $pattern->getScopeName() === $this->grammar->scopeName => $this->grammar->resolve($pattern->getReference()),
                // "include": "scope#name"
                $pattern->getReference() && $pattern->getScopeName() !== $this->grammar->scopeName => $repository->getFromScope($pattern->getScopeName())->resolve($pattern->getReference()),
                // "include": "scope"
                default => $repository->getFromScope($pattern->getScopeName()),
            };
        } catch (UnrecognisedGrammarException $e) {
            if ($this->environment->isStrictModeEnabled()) {
                throw $e;
            }

            return null;
        }
    }

    protected function process(MatchedPattern $matched, int $line, string $lineText): void
    {
        if ($matched->offset() > $this->state->getLinePosition()) {
            $this->tokens[$line][] = new Token(
                $matched->pattern instanceof EndPattern && $matched->pattern->contentName !== null ? [...$this->state->getScopes(), $matched->pattern->contentName] : $this->state->getScopes(),
                substr($lineText, $this->state->getLinePosition(), $matched->offset() - $this->state->getLinePosition()),
                $this->state->getLinePosition(),
                $matched->offset(),
            );

            $this->state->setLinePosition($matched->offset());
        }

        if ($matched->pattern instanceof MatchPattern && $matched->pattern->hasCaptures()) {
            if ($matched->pattern->scope()) {
                $this->state->pushScopes($this->processScope($matched->pattern->scope(), $matched));
            }

            $this->captures($matched, $line, $lineText);

            if ($this->state->getLinePosition() < $matched->end()) {
                $this->tokens[$line][] = new Token(
                    $this->state->getScopes(),
                    substr($lineText, $this->state->getLinePosition(), $matched->end() - $this->state->getLinePosition()),
                    $this->state->getLinePosition(),
                    $matched->end(),
                );

                $this->state->setLinePosition($matched->end());
            }

            if ($matched->pattern->scope()) {
                foreach ($matched->pattern->scope() as $_) {
                    $this->state->popScope();
                }
            }
        } elseif ($matched->pattern instanceof MatchPattern) {
            if ($matched->text() !== '') {
                $this->tokens[$line][] = new Token(
                    $matched->pattern->produceScopes($this->state->getScopes()),
                    $matched->text(),
                    $matched->offset(),
                    $matched->end(),
                );
            }

            $this->state->setLinePosition($matched->end());
        }

        if ($matched->pattern instanceof BeginEndPattern) {
            if ($matched->pattern->scope()) {
                $this->state->pushScopes($this->processScope($matched->pattern->scope(), $matched));
            }

            $this->state->pushAnchorPosition($this->state->getAnchorPosition());

            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->state->getScopes(),
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }

                $this->state->setLinePosition($matched->end());
            }

            $this->state->setAnchorPosition($matched->end());

            /** @phpstan-ignore-next-line method.notFound */
            $endPattern = $matched->pattern->createEndPattern($matched);

            if ($matched->pattern instanceof ProvidesContentName && $matched->pattern->getContentName() !== null) {
                $this->state->pushScope($matched->pattern->getContentName());
            }

            if ($endPattern->hasPatterns()) {
                $this->state->pushPattern($endPattern);

                return;
            }

            if ($matched->pattern instanceof ProvidesContentName && $matched->pattern->getContentName() !== null) {
                $this->state->popScope();
            }

            $this->state->pushPattern($endPattern);

            // $endMatched = $endPattern->tryMatch($this, $lineText, $this->state->getLinePosition());

            // // If we can't see the `end` pattern, we should just return.
            // if ($endMatched === false) {
            //     $this->state->pushPattern($endPattern);

            //     return;
            // }

            // // If we can see the `end` pattern, we should process it.
            // $this->process($endMatched, $line, $lineText);

            // if ($matched->pattern->scope()) {
            //     foreach ($matched->pattern->scope() as $_) {
            //         $this->state->popScope();
            //     }
            // }
        }

        if ($matched->pattern instanceof BeginWhilePattern) {
            if ($matched->pattern->scope()) {
                $this->state->pushScopes($this->processScope($matched->pattern->scope(), $matched));
            }

            $this->state->pushAnchorPosition($this->state->getAnchorPosition());

            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->state->getScopes(),
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }

                $this->state->setLinePosition($matched->end());
            }

            $this->state->setAnchorPosition($matched->end());

            /** @phpstan-ignore-next-line method.notFound */
            $whilePattern = $matched->pattern->createWhilePattern($matched);

            if ($matched->pattern instanceof ProvidesContentName && $matched->pattern->getContentName() !== null) {
                $this->state->pushScope($matched->pattern->getContentName());
            }

            $this->state->pushPattern($whilePattern);

            return;
        }

        if ($matched->pattern instanceof EndPattern) {
            // FIXME: This is a bit of hack. There's a bug somewhere that is incorrectly popping the end scope off
            // of the stack before we're done with that specific scope. This will prevent this from happening.
            if ($matched->pattern->scope()) {
                $potentialMatchedPatternScopes = $this->processScope($matched->pattern->scope(), $matched->pattern->begin);

                foreach ($potentialMatchedPatternScopes as $potentialScope) {
                    if (! in_array($potentialScope, $this->state->getScopes())) {
                        $this->state->pushScope($potentialScope);
                    }
                }
            }

            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->state->getScopes(),
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }
            }

            $this->state->setLinePosition($matched->end());
        }

        if ($matched->pattern instanceof WhilePattern) {
            if ($matched->pattern->hasCaptures()) {
                $this->captures($matched, $line, $lineText);
            } else {
                if ($matched->text() !== '') {
                    $this->tokens[$line][] = new Token(
                        $this->state->getScopes(),
                        $matched->text(),
                        $matched->offset(),
                        $matched->end(),
                    );
                }

                $this->state->setLinePosition($matched->end());
            }

            $this->state->setAnchorPosition($matched->end());
        }
    }

    protected function captures(MatchedPattern $pattern, int $line, string $lineText): void
    {
        if (! $pattern->pattern instanceof ContainsCapturesInterface) {
            throw new IndeterminateStateException('Patterns must implement '.ContainsCapturesInterface::class.' in order to process captures.');
        }

        $captures = $pattern->pattern->getCaptures();

        foreach ($captures as $capture) {
            // Get the capture group.
            $group = $pattern->getCaptureGroup($capture->index);

            // If we can't find the group, or it's invalid (not found), continue.
            if ($group === null || $group[1] === -1) {
                continue;
            }

            if (trim($group[0]) === '') {
                continue;
            }

            $groupLength = strlen($group[0]);
            $groupStart = $group[1];
            $groupEnd = $group[1] + $groupLength;

            // If this group starts after the current position, we need to add a token for the text before the group.
            if ($groupStart > $this->state->getLinePosition()) {
                $this->tokens[$line][] = new Token(
                    $this->state->getScopes(),
                    substr($lineText, $this->state->getLinePosition(), $groupStart - $this->state->getLinePosition()),
                    $this->state->getLinePosition(),
                    $groupStart,
                );

                $this->state->setLinePosition($groupStart);
            }

            // If the capture group has additional scopes, we need to push those on to the stack.
            if ($capture->scope()) {
                $this->state->pushScopes($this->processScope($capture->scope(), $pattern));
            }

            // If the capture has a group of subpatterns, we need to apply them to the matched capture text.
            if ($capture->hasPatterns()) {
                // We need to continue processing the capture group's subpatterns until we reach the end of the matched capture text.
                while ($this->state->getLinePosition() < $groupEnd) {
                    $closest = false;
                    $closestOffset = $this->state->getLinePosition();

                    // We can loop through each of the patterns to find the nearest one that matches.
                    foreach ($capture->getPatterns() as $capturePattern) {
                        $matched = $capturePattern->tryMatch($this, $lineText, $this->state->getLinePosition(), cannotExceed: $groupEnd);

                        // No match found. Move on to next pattern.
                        if ($matched === false) {
                            continue;
                        }

                        // FIXME: I think there's a better way to do this.
                        // Because we're trying to match the capture groups subpattern against the entire line of text,
                        // it will sometimes consume text beyond the capture group. This is a hack to prevent that, but
                        // I think we need to find a better way of doing these subpattern matches.
                        if ($matched->end() > $groupEnd) {
                            $matched->matches[0][0] = substr($matched->matches[0][0], 0, $groupEnd - $matched->end());
                        }

                        // Match found and is same as current position. Return it.
                        if ($matched->offset() === $this->state->getLinePosition()) {
                            $closest = $matched;
                            $closestOffset = $this->state->getLinePosition() + $matched->offset();

                            break;
                        }

                        // First match found. Set it as the closest one.
                        if ($closest === false) {
                            $closest = $matched;
                            $closestOffset = $matched->offset();

                            continue;
                        }

                        // Match found, closer than previous one.
                        if ($matched->offset() < $closestOffset) {
                            $closest = $matched;
                            $closestOffset = $matched->offset();

                            continue;
                        }
                    }

                    // If we don't find a match, we can consume the rest of the matched capture text and move on.
                    if ($closest === false) {
                        $this->tokens[$line][] = new Token(
                            $this->state->getScopes(),
                            substr($lineText, $this->state->getLinePosition(), $groupEnd - $this->state->getLinePosition()),
                            $this->state->getLinePosition(),
                            $groupEnd,
                        );

                        $this->state->setLinePosition($groupEnd);

                        break;
                    }

                    // If we find a MatchPattern, we can process it normally.
                    if ($closest->pattern instanceof MatchPattern) {
                        $this->process($closest, $line, $lineText);
                    } elseif ($closest->pattern instanceof BeginEndPattern) {
                        // If we find a BeginEndPattern, we need to handle it here so that
                        // it doesn't affect the stack.

                        // We start by pushing the pattern's scope onto the stack.
                        if ($closest->pattern->scope()) {
                            $this->state->pushScopes($this->processScope($closest->pattern->scope(), $closest));
                        }

                        // If the matched pattern has it's own set of captures, we need to process those here.
                        if ($closest->pattern->hasCaptures()) {
                            $this->captures($closest, $line, $lineText);
                        } else {
                            // Otherwise, if the matched text isn't an empty string, we can create a token for it.
                            if ($closest->text() !== '') {
                                $this->tokens[$line][] = new Token(
                                    $this->state->getScopes(),
                                    $closest->text(),
                                    $closest->offset(),
                                    $closest->end(),
                                );
                            }

                            $this->state->setLinePosition($closest->end());
                        }

                        // We now need to create a new EndPattern for the BeginEndPattern and handle it inline.
                        /** @phpstan-ignore-next-line method.notFound */
                        $endPattern = $closest->pattern->createEndPattern($closest);

                        // If the EndPattern has some patterns (things to match between the begin and end), we can start processing those.
                        if ($endPattern->hasPatterns()) {
                            // We can create a CollectionPattern from those patterns.
                            $onlyPatternsPattern = new CollectionPattern($endPattern->getPatterns());

                            // As long as we don't reach the end of the group, we can try to match a pattern.
                            while ($this->state->getLinePosition() < $groupEnd) {
                                $subPatternMatched = $onlyPatternsPattern->tryMatch($this, $lineText, $this->state->getLinePosition(), $groupEnd);

                                // If we match a subpattern, we need to check to see if the end matches since that takes priority.
                                if ($subPatternMatched !== false && $endPattern instanceof EndPattern && $endMatched = $endPattern->tryMatch($this, $lineText, $this->state->getLinePosition())) {
                                    // If the end does match, then we can break out of this loop and process the end pattern normally.
                                    if ($endMatched->offset() <= $subPatternMatched->offset()) {
                                        break;
                                    }
                                }

                                // If we haven't found a subpattern, we need to break out of this loop 
                                // since we should now be able to match the end pattern.
                                //
                                // If we can't find the end pattern after this, then the grammar is incorrect :D
                                if ($subPatternMatched === false) {
                                    break;
                                }

                                // We've found a matching subpattern, so we can process it accordingly.
                                $this->process($subPatternMatched, $line, $lineText);

                                // If the subpattern has additional scopes that were pushed to the stack,
                                // we need to pop them off since we're done with subpattern.
                                if ($subPatternMatched->pattern->scope()) {
                                    foreach ($subPatternMatched->pattern->scope() as $_) {
                                        $this->state->popScope();
                                    }
                                }
                            }
                        }

                        $endMatched = $endPattern->tryMatch($this, $lineText, $this->state->getLinePosition());

                        // If we can't see the `end` pattern, we should just continue.
                        if ($endMatched === false) {
                            continue;
                        }

                        // If we can see the `end` pattern, we should process it.
                        $this->process($endMatched, $line, $lineText);

                        if ($closest->pattern->scope()) {
                            foreach ($closest->pattern->scope() as $_) {
                                $this->state->popScope();
                            }
                        }
                    }
                }

                $this->state->setLinePosition($groupEnd);
            } elseif ($group[0] !== '') {
                // If the group doesn't have any subpatterns, we can just add the token.
                $token = new Token(
                    $this->state->getScopes(),
                    $group[0],
                    $groupStart,
                    $groupEnd,
                );

                // In some rare cases, we need to modify existing tokens and splice new ones in.
                if ($token->start < $this->state->getLinePosition()) {
                    $newTokens = [];

                    for ($i = count($this->tokens[$line]) - 1; $i >= 0; $i--) {
                        $previous = $this->tokens[$line][$i];

                        // New token starts before this token.
                        if ($token->start < $previous->start) {
                            continue;
                        }

                        // New token ends after this token. This should in theory never happen since this capture group is nested
                        // meaning it can't theoretically end after the target token.
                        if ($token->end > $previous->end) {
                            break;
                        }

                        $newPrevious = clone $previous;
                        $newPrevious->text = substr($previous->text, 0, $token->start - $previous->start);
                        $newPrevious->end = $token->start;

                        if ($newPrevious->text !== '') {
                            $newTokens[] = $newPrevious;
                        }

                        $token->scopes = $this->mergeScopes($previous->scopes, $token->scopes);

                        $newTokens[] = $token;

                        $postText = substr($previous->text, $token->end - $previous->start);
                        $postStart = $token->end;

                        if ($postText !== '') {
                            $newTokens[] = new Token(
                                $previous->scopes,
                                $postText,
                                $postStart,
                                $previous->end,
                            );
                        }

                        array_splice($this->tokens[$line], $i, 1, $newTokens);
                    }
                } else {
                    // Most of the time, we can just add the token and move on.
                    $this->tokens[$line][] = $token;
                    $this->state->setLinePosition($groupEnd);
                }
            }

            // If the capture group has additional scopes, we need to pop those off the stack.
            if ($capture->scope()) {
                foreach ($capture->scope() as $_) {
                    $this->state->popScope();
                }
            }
        }

        // If there is any text left in the line after processing the captures, we need to consume it before moving on.
        if ($this->state->getLinePosition() < $pattern->end()) {
            $this->tokens[$line][] = new Token(
                $this->state->getScopes(),
                substr($lineText, $this->state->getLinePosition(), $pattern->end() - $this->state->getLinePosition()),
                $this->state->getLinePosition(),
                $pattern->end(),
            );

            $this->state->setLinePosition($pattern->end());
        }
    }

    protected function mergeScopes(array $a, array $b): array
    {
        $scopes = array_merge($a, $b);

        return array_values(array_unique($scopes));
    }

    protected function processScope(string|array $scope, MatchedPattern $pattern): array
    {
        $scopes = is_array($scope) ? $scope : [$scope];

        return array_map(function (string $scope) use ($pattern) {
            return preg_replace_callback('/\\$(\d+)/', function ($matches) use ($pattern) {
                $group = $pattern->getCaptureGroup($matches[1]);

                if ($group === null) {
                    return $matches[0];
                }

                return $group[0];
            }, $scope);
        }, $scopes);
    }

    public function allowA(): bool
    {
        return $this->state->isFirstLine();
    }

    public function allowG(): bool
    {
        return $this->state->getLinePosition() === $this->state->getAnchorPosition();
    }

    // FIXME: This should actually check all while conditions on the patternStack
    // to see which ones need to be popped off.
    //
    // This isn't super easy to do right now since we don't have a true "stack"
    // of Tokenizer state, but rather a single mutable set of properties.
    //
    // In order to make this work exactly how it's supposed to, we need to refactor
    // so that we have a more robust stack with pushing and popping so we can easily
    // reassign the properties of the Tokenizer to the previous state.
    protected function checkWhileConditions(int $line, string $lineText): void
    {
        $root = $this->state->getPattern();

        // If we've got a `while` pattern on the stack and it doesn't match the current line, we need to pop
        // it off and handle it accordingly.
        if ($root instanceof WhilePattern) {
            $whileMatched = $root->tryMatch($this, $lineText, $this->state->getLinePosition());

            if (! $whileMatched) {
                $poppedPattern = $this->state->popPattern();

                if ($poppedPattern->wasInjected()) {
                    $this->state->resetActiveInjection();
                }

                if ($root->contentName) {
                    $this->state->popScope();
                }

                if ($root->scope()) {
                    foreach ($root->scope() as $_) {
                        $this->state->popScope();
                    }
                }

                $this->state->setAnchorPosition($this->state->popAnchorPosition());

                return;
            }

            $this->process($whileMatched, $line, $lineText);
        }
    }
}
