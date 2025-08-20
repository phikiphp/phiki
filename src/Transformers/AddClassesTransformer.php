<?php

namespace Phiki\Transformers;

use Phiki\Phast\ClassList;
use Phiki\Phast\Element;
use Phiki\Token\HighlightedToken;

class AddClassesTransformer extends AbstractTransformer
{
    /**
     * Create a new instance.
     */
    public function __construct(private bool $retainStyles = true) {}

    /**
     * Modify the `<span>` for each token.
     */
    public function token(Element $span, HighlightedToken $token, int $index, int $line): Element
    {
        $classList = $span->properties->get('class') ?? new ClassList();
        
        foreach ($token->token->scopes as $scope) {
            $classList->add('phiki-' . $scope);
        }

        $span->properties->set('class', $classList);

        if (! $this->retainStyles) {
            $span->properties->remove('style');
        }

        return $span;
    }
}
