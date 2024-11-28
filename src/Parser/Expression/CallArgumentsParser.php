<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class CallArgumentsParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * 解析调用参数
     *
     * @return Expression[] 返回的数组包含调用参数
     */
    public function parse(): array
    {
        $args = [];

        // 如果下一个 Token 是右括号，表示参数列表为空
        if ($this->parser->peekTokenIs(TokenType::RPAREN)) {
            $this->parser->next(); // 跳过右括号

            return $args;
        }

        $this->parser->next(); // 移动到第一个参数
        $args[] = $this->parser->parseExpression(PrecedenceLevel::LOWEST);

        // 解析剩余参数
        while ($this->parser->peekTokenIs(TokenType::COMMA)) {
            $this->parser->next(); // 跳过逗号
            $this->parser->next(); // 跳到下一个参数
            $args[] = $this->parser->parseExpression(PrecedenceLevel::LOWEST);
        }

        // 验证是否有右括号
        if (! $this->parser->expectPeek(TokenType::RPAREN)) {
            return [];
        }

        return $args;
    }
}
