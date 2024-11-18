<?php

namespace Summer\West\Parser;

use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
use Summer\West\Token\TokenType;

class LetStatementParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?LetStatement
    {
        $letToken = $this->parser->getCurToken();

        if (! $this->parser->expectPeek(TokenType::IDENT)) {
            $this->parser->addError("Expected IDENT after LET, but got {$this->parser->getPeekToken()?->type->name}");

            return null;
        }

        $name = new Identifier($this->parser->getCurToken(), $this->parser->getCurToken()->literal);
        $stmt = new LetStatement($letToken, $name, null);

        if (! $this->parser->expectPeek(TokenType::ASSIGN)) {
            $this->parser->addError("Expected ASSIGN after IDENT, but got {$this->parser->getPeekToken()?->type->name}");

            return null;
        }

        while (
            $this->parser->getCurToken()->type !== TokenType::SEMICOLON &&
            $this->parser->getCurToken()->type !== TokenType::EOF
        ) {
            $this->parser->next();
        }

        return $stmt;
    }
}
