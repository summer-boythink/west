<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\ArrayLiteral;
use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class ArrayLiteralParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $array = new ArrayLiteral(
            $this->parser->getCurToken(),
            $this->parser->parseExpressionList(TokenType::RBRACKET)
        );

        return $array;
    }
}
