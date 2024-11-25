<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\PrefixExpression;
use Summer\West\Parser\Parser;
use Summer\West\Parser\Precedence;

class PrefixExpressionParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $operator = $this->parser->getCurToken()->literal;

        $this->parser->next(); // 移动到操作符右边的表达式

        $right = $this->parser->parseExpression(Precedence::PREFIX);

        return new PrefixExpression(
            $this->parser->getCurToken(),
            $operator,
            $right
        );
    }
}
