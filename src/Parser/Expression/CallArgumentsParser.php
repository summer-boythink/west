<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;
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
        return $this->parser->parseExpressionList(TokenType::RPAREN);
    }
}
