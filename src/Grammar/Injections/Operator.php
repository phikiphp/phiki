<?php

namespace Phiki\Grammar\Injections;

/** @internal */
enum Operator
{
    case Or;
    case And;
    case Not;
    case None;
}
