<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\IndexExpression;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class IndexExpressionParser implements IinfixExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Expression $left): ?Expression
    {

        $token = $this->parser->getCurToken();

        // 解析索引部分的表达式
        $this->parser->next();
        $index = $this->parser->parseExpression(PrecedenceLevel::LOWEST);
        // 创建一个 IndexExpression 实例
        $indexExpression = new IndexExpression(
            $token,
            $left,
            $index
        );

        // 确保有右括号 `]`
        if (! $this->parser->expectPeek(TokenType::RBRACKET)) {
            return null;
        }

        return $indexExpression;
    }
}
