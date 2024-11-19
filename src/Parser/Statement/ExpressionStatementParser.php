<?php

namespace Summer\West\Parser\Statement;

use Summer\West\Ast\ExpressionStatement;
use Summer\West\Parser\Parser;
use Summer\West\Parser\Precedence;
use Summer\West\Token\TokenType;

class ExpressionStatementParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?ExpressionStatement
    {
        // 创建 ExpressionStatement 节点
        $stmt = new ExpressionStatement($this->parser->getCurToken(), null);

        // 解析表达式
        $stmt->expression = $this->parser->parseExpression(Precedence::LOWEST); // LOWEST 优先级为 0

        // 如果下一个 token 是分号，则跳过
        if ($this->parser->peekTokenIs(TokenType::SEMICOLON)) {
            $this->parser->next();
        }

        return $stmt;
    }
}
