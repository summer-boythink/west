<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\IfExpression;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class IfExpressionParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $token = $this->parser->getCurToken();

        // 解析左括号
        if (! $this->parser->expectPeek(TokenType::LPAREN)) {
            return null;
        }

        $this->parser->next(); // 移动到条件表达式
        $condition = $this->parser->parseExpression(PrecedenceLevel::LOWEST);

        // 解析右括号
        if (! $this->parser->expectPeek(TokenType::RPAREN)) {
            return null;
        }

        // 解析左大括号
        if (! $this->parser->expectPeek(TokenType::LBRACE)) {
            return null;
        }

        $consequence = $this->parser->parseBlockStatement();

        // 检查是否有 else 块
        $alternative = null;
        if ($this->parser->peekTokenIs(TokenType::ELSE)) {
            $this->parser->next(); // 跳过 else
            if (! $this->parser->expectPeek(TokenType::LBRACE)) {
                return null;
            }
            $alternative = $this->parser->parseBlockStatement();
        }

        return new IfExpression(
            $token,
            $condition,
            $consequence,
            $alternative
        );
    }
}
