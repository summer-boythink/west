<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\CallExpression;
use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;

class CallExpressionParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(Expression $function): ?Expression
    {
        $token = $this->parser->getCurToken();

        // 解析调用参数
        $arguments = $this->parser->parseCallArguments();

        return new CallExpression($token, $function, $arguments);
    }
}
