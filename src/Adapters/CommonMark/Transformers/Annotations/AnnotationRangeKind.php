<?php

namespace Phiki\Adapters\CommonMark\Transformers\Annotations;

enum AnnotationRangeKind
{
    case Fixed;
    case OpenEnded;
    case End;
}
