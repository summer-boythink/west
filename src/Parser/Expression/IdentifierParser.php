<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\Identifier;
use Summer\West\Parser\Parser;

class IdentifierParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): Expression
    {
        return new Identifier($this->parser->getCurToken(), $this->parser->getCurToken()->literal);
    }
}
