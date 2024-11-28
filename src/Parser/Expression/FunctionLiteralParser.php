<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\FunctionLiteral;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class FunctionLiteralParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $token = $this->parser->getCurToken();

        // 验证是否有左括号
        if (! $this->parser->expectPeek(TokenType::LPAREN)) {
            return null;
        }

        // 解析参数列表
        $parameters = $this->parser->parseFunctionParameters();

        // 验证是否有左大括号
        if (! $this->parser->expectPeek(TokenType::LBRACE)) {
            return null;
        }

        // 解析函数体
        $body = $this->parser->parseBlockStatement();

        return new FunctionLiteral($token, $parameters, $body);
    }
}
