<?php

namespace Phiki\Grammar\Injections;

enum Operator
{
    case Or;
    case And;
    case Not;
    case None;
}
