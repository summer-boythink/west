<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Identifier;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class FunctionParametersParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * parser func params
     *
     * @return Identifier[]
     */
    public function parse(): array
    {
        $parameters = [];

        // 如果下一个 Token 是右括号，表示参数列表为空
        if ($this->parser->peekTokenIs(TokenType::RPAREN)) {
            $this->parser->next(); // 跳过右括号

            return $parameters;
        }

        $this->parser->next(); // 移动到第一个参数
        $parameters[] = new Identifier($this->parser->getCurToken(), $this->parser->getCurToken()->literal);

        // 解析剩余的参数
        while ($this->parser->peekTokenIs(TokenType::COMMA)) {
            $this->parser->next(); // 跳过逗号
            $this->parser->next(); // 跳到下一个参数
            $parameters[] = new Identifier($this->parser->getCurToken(), $this->parser->getCurToken()->literal);
        }

        // 验证是否有右括号
        if (! $this->parser->expectPeek(TokenType::RPAREN)) {
            return [];
        }

        return $parameters;
    }
}
