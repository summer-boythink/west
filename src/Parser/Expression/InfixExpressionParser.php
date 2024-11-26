<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\InfixExpression;
use Summer\West\Parser\Parser;

class InfixExpressionParser implements IinfixExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Expression $left): ?Expression
    {
        $operator = $this->parser->getCurToken()->literal;
        $precedence = $this->parser->getCurrentPrecedence();

        $this->parser->next(); // 跳过操作符，解析右表达式
        $right = $this->parser->parseExpression($precedence);

        return new InfixExpression(
            $this->parser->getCurToken(),
            $left,
            $operator,
            $right
        );
    }
}
