<?php

namespace Phiki\TextMate;

use Phiki\Contracts\PatternInterface;
use Phiki\Environment;
use Phiki\Grammar\BeginEndPattern;
use Phiki\Grammar\BeginWhilePattern;
use Phiki\Grammar\EndPattern;
use Phiki\Grammar\Injections\Injection;
use Phiki\Grammar\Injections\Prefix;
use Phiki\Grammar\MatchedInjection;
use Phiki\Grammar\MatchedPattern;
use Phiki\Grammar\MatchPattern;
use Phiki\Grammar\ParsedGrammar;
use Phiki\Grammar\WhilePattern;
use Phiki\Token\Token;

class Tokenizer
{
    /**
     * Compiled patterns for the current tokenize() call, keyed by pattern, base grammar and anchor flags.
     * Each entry holds its pattern so the object ID in the key can't be reused while the cache is alive.
     *
     * @var array<string, array{ 0: PatternInterface, 1: array<array{ 0: PatternInterface, 1: string }> }>
     */
    protected array $compiled = [];

    /**
     * Results of earlier regex searches on the line being tokenized, for the current tokenize() call.
     */
    protected ?SearchResultCache $searches = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected ParsedGrammar $grammar,
        protected Environment $environment,
    ) {}

    /**
     * Tokenize the given text.
     *
     * @return array<Token[]>
     */
    public function tokenize(string $text): array
    {
        $rootScopeName = $this->grammar->scopeName;
        $scopeList = AttributedScopeStack::createRoot($rootScopeName);
        $stateStack = new StateStack(
            parent: null,
            pattern: $this->grammar,
            enterPos: -1,
            anchorPos: -1,
            beginRuleCapturedEOL: false,
            endRule: null,
            nameScopesList: $scopeList,
            contentNameScopesList: $scopeList,
        );

        $lines = array_values(preg_split("/\R/u", $text));
        $tokens = [];
        $this->compiled = [];
        $this->searches = new SearchResultCache;

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $stateStack->reset();
            }

            $lineText = $line."\n";
            $lineLength = strlen($lineText);
            $lineTokens = new LineTokens($lineText);

            $this->tokenizeString($this->grammar, $lineText, $index === 0, 0, $stateStack, $lineTokens, true);

            $tokens[] = $lineTokens->getResult($stateStack, $lineLength);
        }

        $this->compiled = [];
        $this->searches = null;

        return $tokens;
    }

    /**
     * Tokenize the given string.
     */
    protected function tokenizeString(ParsedGrammar $grammar, string $lineText, bool $isFirstLine, int $linePos, StateStack &$stack, LineTokens $lineTokens, bool $checkWhileConditions): void
    {
        $lineLength = strlen($lineText);
        $anchorPosition = -1;

        if ($checkWhileConditions) {
            $this->checkWhileConditions($grammar, $lineText, $isFirstLine, $linePos, $stack, $lineTokens, $anchorPosition);
        }

        $stop = false;

        while (! $stop) {
            // Find the next matching rule or injection.
            $rule = $this->matchRuleOrInjections($grammar, $lineText, $isFirstLine, $linePos, $stack, $anchorPosition);

            // If there is no match, we are done.
            if ($rule === null) {
                $lineTokens->produce($stack, $lineLength);
                $stop = true;

                continue;
            }

            // We need to check to see if we've advanced so that we don't end up in an infinite loop.
            $hasAdvanced = count($rule->matches) > 0 ? $rule->end() > $linePos : false;

            // If the pattern we matched was an `end` pattern, we need to change how we process it.
            if ($rule->pattern instanceof EndPattern) {
                // We first want to produce the tokens on this line up until the start of the match,
                // otherwise we'll end up skipping text that came before the match.
                $lineTokens->produce($stack, $rule->offset());

                // We can then modify the stack to include the correct scope names.
                $stack = $stack->withContentNameScopesList($stack->nameScopesList);

                // Now we can process the captures for this matched pattern.
                $this->handleCaptures($grammar, $lineText, $isFirstLine, $stack, $lineTokens, $rule->pattern->captures(), $rule->matches);

                // We now need to make sure we produce tokens for the remaining text in the match.
                $lineTokens->produce($stack, $rule->end());

                // To prevent infinite loops, we need to see whether or not we've advanced between the last match
                // and this one. We can do this by comparing the enter position of the stack and parent stack.
                $popped = clone $stack;
                // @phpstan-ignore-next-line parameterByRef.type
                $stack = $stack->pop();
                $anchorPosition = $popped->anchorPos;

                // If we've not advanced and the point at which we entered the current stack frame is the same
                // as the current line position then we know we're going to enter an infinite loop, so we can
                // process the rest of the line accordingly and stop processing this line.
                if (! $hasAdvanced && $popped->enterPos === $linePos) {
                    $stack = $popped;
                    $lineTokens->produce($stack, $lineLength);
                    $stop = true;

                    continue;
                }
            } else {
                // If any other type of pattern is matched, we can process that accordingly too.

                // We start by consuming all of the text up until the point of the match.
                $lineTokens->produce($stack, $rule->offset());

                $beforePush = clone $stack;

                // We can then create a new scope stack for the matched pattern.
                $scopeName = $rule->pattern->getScopeName($rule->matches);
                $nameScopesList = $stack->contentNameScopesList->push($scopeName);

                // And push that onto the stack.
                $stack = $stack->push($rule->pattern, $linePos, $anchorPosition, $rule->end() === $lineLength, null, $nameScopesList, $nameScopesList);

                // If we matched a `begin` `end` pattern, we need to handle the captures for that.
                if ($rule->pattern instanceof BeginEndPattern) {
                    $this->handleCaptures($grammar, $lineText, $isFirstLine, $stack, $lineTokens, $rule->pattern->captures(), $rule->matches);

                    $lineTokens->produce($stack, $rule->end());
                    $anchorPosition = $rule->end();

                    $contentName = $rule->pattern->getContentName($rule->matches);
                    $contentNameScopesList = $nameScopesList->push($contentName);

                    $stack = $stack->withContentNameScopesList($contentNameScopesList)->withEndRule($rule->pattern->createEndPattern($rule));

                    if (! $hasAdvanced && $beforePush->hasSameRuleAs($stack)) {
                        // @phpstan-ignore-next-line parameterByRef.type
                        $stack = $stack->pop();
                        $lineTokens->produce($stack, $lineLength);
                        $stop = true;

                        continue;
                    }
                } elseif ($rule->pattern instanceof BeginWhilePattern) {
                    $this->handleCaptures($grammar, $lineText, $isFirstLine, $stack, $lineTokens, $rule->pattern->captures(), $rule->matches);

                    $lineTokens->produce($stack, $rule->end());
                    $anchorPosition = $rule->end();

                    $contentName = $rule->pattern->getContentName($rule->matches);
                    $contentNameScopesList = $nameScopesList->push($contentName);

                    $stack = $stack->withContentNameScopesList($contentNameScopesList)->withEndRule($rule->pattern->createWhilePattern($rule));

                    if (! $hasAdvanced && $beforePush->hasSameRuleAs($stack)) {
                        // @phpstan-ignore-next-line parameterByRef.type
                        $stack = $stack->pop();
                        $lineTokens->produce($stack, $lineLength);
                        $stop = true;

                        continue;
                    }
                } elseif ($rule->pattern instanceof MatchPattern) {
                    $this->handleCaptures($grammar, $lineText, $isFirstLine, $stack, $lineTokens, $rule->pattern->captures, $rule->matches);

                    $lineTokens->produce($stack, $rule->end());

                    // @phpstan-ignore-next-line parameterByRef.type
                    $stack = $stack->pop();

                    if (! $hasAdvanced) {
                        $stack = $stack->safePop();
                        $lineTokens->produce($stack, $lineLength);
                        $stop = true;

                        continue;
                    }
                }
            }

            if ($rule->end() > $linePos) {
                $linePos = $rule->end();
                $isFirstLine = false;
            }
        }
    }

    /**
     * Handle the captures for a matched pattern.
     */
    protected function handleCaptures(ParsedGrammar $grammar, string $lineText, bool $isFirstLine, StateStack $stack, LineTokens $lineTokens, ?array $captures, array $matches): void
    {
        if ($captures === null || count(array_filter($captures)) === 0) {
            return;
        }

        $len = min(count($captures), count($matches));
        /** @var LocalStackElement[] $localStack */
        $localStack = [];
        $maxEnd = strlen($matches[0][0]) + $matches[0][1];

        for ($i = 0; $i < $len; $i++) {
            $captureRule = $captures[$i] ?? null;

            if (! $captureRule) {
                continue;
            }

            $match = $matches[$i];

            if ($match[0] === '') {
                continue;
            }

            if ($match[1] > $maxEnd) {
                break;
            }

            while (count($localStack) > 0 && $localStack[count($localStack) - 1]->endPos <= $match[1]) {
                $stackElement = $localStack[count($localStack) - 1];
                $lineTokens->produceFromScopes($stackElement->scopes, $stackElement->endPos);
                array_pop($localStack);
            }

            if (count($localStack) > 0) {
                $stackElement = $localStack[count($localStack) - 1];

                $lineTokens->produceFromScopes($stackElement->scopes, $match[1]);
            } else {
                $lineTokens->produce($stack, $match[1]);
            }

            if ($captureRule->retokenizeCapturedWithRule()) {
                $scopeName = $captureRule->getScopeName($matches);
                $nameScopesList = $stack->contentNameScopesList->push($scopeName);
                $stackClone = $stack->push($captureRule->pattern, $match[1], -1, false, null, $nameScopesList, $nameScopesList);
                $this->tokenizeString($grammar, substr($lineText, 0, strlen($match[0]) + $match[1]), $isFirstLine && $match[1] === 0, $match[1], $stackClone, $lineTokens, false);

                continue;
            }

            $captureRuleScopeName = $captureRule->getScopeName($matches);

            if ($captureRuleScopeName !== null) {
                $base = count($localStack) > 0 ? $localStack[count($localStack) - 1]->scopes : $stack->contentNameScopesList;
                $captureRuleScopesList = $base->push($captureRuleScopeName);
                $localStack[] = new LocalStackElement($captureRuleScopesList, strlen($match[0]) + $match[1]);
            }
        }

        while (count($localStack) > 0) {
            $stackElement = $localStack[count($localStack) - 1];
            $lineTokens->produceFromScopes($stackElement->scopes, $stackElement->endPos);
            array_pop($localStack);
        }
    }

    /**
     * Match a rule or injection.
     */
    protected function matchRuleOrInjections(ParsedGrammar $grammar, string $lineText, bool $isFirstLine, int $linePos, StateStack &$stack, int $anchorPosition): ?MatchedPattern
    {
        $matchResult = $this->matchRule($grammar, $lineText, $isFirstLine, $linePos, $stack, $anchorPosition);

        if (! $grammar->hasInjections()) {
            return $matchResult;
        }

        $injectionResult = $this->matchInjections($grammar->getInjections(), $grammar, $lineText, $isFirstLine, $linePos, $stack, $anchorPosition);

        if (! $injectionResult) {
            return $matchResult;
        }

        if (! $matchResult) {
            return $injectionResult->matchedPattern;
        }

        $matchResultScore = $matchResult->offset();
        $injectionResultScore = $injectionResult->offset();

        if ($injectionResultScore < $matchResultScore || ($injectionResult->prefix === Prefix::Left && $injectionResultScore === $matchResultScore)) {
            return $injectionResult->matchedPattern;
        }

        return $matchResult;
    }

    /**
     * Try to match a rule.
     */
    protected function matchRule(ParsedGrammar $grammar, string $lineText, bool $isFirstLine, int $linePos, StateStack &$stack, int $anchorPosition): ?MatchedPattern
    {
        $allowG = $linePos === $anchorPosition;
        $compiled = $this->compile($stack->pattern, $grammar, $isFirstLine, $allowG);

        return (new PatternSearcher($stack->pattern, $grammar, $this->environment->grammars, $isFirstLine, $allowG, $this->environment->engine, $compiled, $this->searches))
            ->findNextMatch($lineText, $linePos);
    }

    /**
     * Compile a pattern, reusing the result from earlier in this tokenize() call when possible.
     *
     * @return array<array{ 0: PatternInterface, 1: string }>
     */
    protected function compile(PatternInterface $pattern, ParsedGrammar $grammar, bool $allowA, bool $allowG): array
    {
        // End and while rules are created for each begin match, and an end regex can refer to the
        // begin captures, so it's resolved every time. Their nested patterns come from the begin
        // rule and don't change, so those are cached against the begin rule instead.
        $nestedOnly = $pattern instanceof EndPattern || $pattern instanceof WhilePattern;
        $owner = $nestedOnly ? $pattern->begin->pattern : $pattern;
        $key = ($nestedOnly ? 'nested:' : '').spl_object_id($owner).':'.spl_object_id($grammar).':'.(int) $allowA.(int) $allowG;

        if (! isset($this->compiled[$key])) {
            $compiled = $pattern->compile($grammar, $this->environment->grammars, $allowA, $allowG);
            $nested = $compiled;

            // The end regex comes first, or last when `applyEndPatternLast` is set.
            if ($pattern instanceof EndPattern) {
                $nested = $pattern->applyEndPatternLast ? array_slice($compiled, 0, -1) : array_slice($compiled, 1);
            }

            $this->compiled[$key] = [$owner, $nested];

            return $compiled;
        }

        $nested = $this->compiled[$key][1];

        if ($pattern instanceof EndPattern) {
            $end = [$pattern, $pattern->end->get($allowA, $allowG, $pattern->begin->matches)];

            return $pattern->applyEndPatternLast ? [...$nested, $end] : [$end, ...$nested];
        }

        return $nested;
    }

    /**
     * Try to match an injection.
     *
     * @param  array<Injection>  $injections
     */
    protected function matchInjections(array $injections, ParsedGrammar $grammar, string $lineText, bool $isFirstLine, int $linePos, StateStack $stack, int $anchorPosition): ?MatchedInjection
    {
        $bestMatchRating = PHP_INT_MAX;
        $bestMatchedInjection = null;
        $bestMatchedPattern = null;

        $scopes = $stack->contentNameScopesList->getScopeNames();
        $len = count($injections);

        usort($injections, fn (Injection $a, Injection $b) => ($a->getPrefix($scopes)->value ?? 0) <=> ($b->getPrefix($scopes)->value ?? 0));

        for ($i = 0; $i < $len; $i++) {
            $injection = $injections[$i];

            if (! $injection->matches($scopes)) {
                continue;
            }

            $allowG = $linePos === $anchorPosition;
            $compiled = $this->compile($injection, $grammar, $isFirstLine, $allowG);
            $searcher = new PatternSearcher($injection, $grammar, $this->environment->grammars, $isFirstLine, $allowG, $this->environment->engine, $compiled, $this->searches);
            $matched = $searcher->findNextMatch($lineText, $linePos);

            if (! $matched) {
                continue;
            }

            $matchRating = $matched->offset();
            if ($matchRating >= $bestMatchRating) {
                continue;
            }

            $bestMatchRating = $matchRating;
            $bestMatchedInjection = $injection;
            $bestMatchedPattern = $matched;

            if ($bestMatchRating === $linePos) {
                break;
            }
        }

        if ($bestMatchedInjection === null) {
            return null;
        }

        return new MatchedInjection($bestMatchedInjection, $bestMatchedPattern, $bestMatchedInjection->getPrefix($scopes));
    }

    /**
     * Check while conditions on the stack.
     */
    protected function checkWhileConditions(ParsedGrammar $grammar, string $lineText, bool &$isFirstLine, int &$linePos, StateStack &$stack, LineTokens $lineTokens, int &$anchorPosition): void
    {
        $anchorPosition = $stack->beginRuleCapturedEOL ? 0 : -1;

        $whileRules = [];

        // Find all of the while patterns in the stack.
        for ($node = $stack; $node; $node = $node->pop()) {
            if ($node->pattern instanceof WhilePattern) {
                $whileRules[] = new WhileStackElement($node, $node->pattern);
            }
        }

        // Process and check each while pattern.
        for ($whileRule = array_pop($whileRules); $whileRule; $whileRule = array_pop($whileRules)) {
            $searcher = new PatternSearcher($whileRule->rule, $grammar, $this->environment->grammars, $isFirstLine, $linePos === $anchorPosition, $this->environment->engine, searches: $this->searches);
            $r = $searcher->findNextMatch($lineText, $linePos, while: true);

            if (! $r) {
                // @phpstan-ignore-next-line parameterByRef.type
                $stack = $whileRule->stack->pop();
                break;
            }

            if (count($r->matches) > 0) {
                $lineTokens->produce($whileRule->stack, $r->offset());
                $this->handleCaptures($grammar, $lineText, $isFirstLine, $whileRule->stack, $lineTokens, $whileRule->rule->captures(), $r->matches);
                $lineTokens->produce($whileRule->stack, $r->end());
                $anchorPosition = $r->end();
                if ($r->end() > $linePos) {
                    $linePos = $r->end();
                    $isFirstLine = false;
                }
            }
        }
    }
}
