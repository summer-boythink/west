<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\StringLiteral;
use Summer\West\Parser\Parser;

class StringLiteralParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $literal = new StringLiteral($this->parser->getCurToken(),
            $this->parser->getCurToken()->getLiteral());

        return $literal;
    }
}
