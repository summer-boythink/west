<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class GroupedExpressionParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $this->parser->next(); // 跳过左括号

        $expression = $this->parser->parseExpression(PrecedenceLevel::LOWEST);

        if (! $this->parser->expectPeek(TokenType::RPAREN)) {
            return null; // 如果右括号不存在，返回 null 或记录错误
        }

        return $expression;
    }
}
