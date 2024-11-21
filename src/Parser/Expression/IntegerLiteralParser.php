<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\Expression;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Parser\Parser;

class IntegerLiteralParser implements IExpression
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?Expression
    {
        $literal = new IntegerLiteral($this->parser->getCurToken(), null);

        $value = filter_var($this->parser->getCurToken()->literal, FILTER_VALIDATE_INT);
        if ($value === false) {
            $this->parser->addError(sprintf('could not parse %q as integer', $this->parser->getCurToken()->literal));

            return null;
        }

        $literal->value = $value;

        return $literal;
    }
}
