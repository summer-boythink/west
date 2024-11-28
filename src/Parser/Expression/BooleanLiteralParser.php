<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\BooleanLiteral;
use Summer\West\Ast\Expression;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class BooleanLiteralParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $isTrue = $this->parser->curTokenIs(TokenType::TRUE);

        return new BooleanLiteral(
            $this->parser->getCurToken(),
            $isTrue
        );
    }
}
