<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;

interface IinfixExpression
{
    public function parse(Expression $left): ?Expression;
}
