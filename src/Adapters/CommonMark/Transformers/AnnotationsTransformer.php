<?php

namespace Phiki\Adapters\CommonMark\Transformers;

use Phiki\Contracts\RequiresGrammarInterface;
use Phiki\Transformers\AbstractTransformer;
use Phiki\Transformers\Concerns\RequiresGrammar;

class AnnotationsTransformer extends AbstractTransformer implements RequiresGrammarInterface
{
    use RequiresGrammar;
}
