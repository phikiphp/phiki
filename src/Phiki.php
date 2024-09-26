<?php

namespace Phiki;

class Phiki
{
    public function __construct(
        protected GrammarRepository $grammarRepository = new GrammarRepository,
    ) {}
}