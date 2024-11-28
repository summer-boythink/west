<?php

namespace Summer\West\Parser\Statement;

use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class LetStatementParser implements IStatement
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
        $this->parser->next();

        $stmt->value = $this->parser->parseExpression(PrecedenceLevel::LOWEST);

        if (
            $this->parser->peekTokenIs(TokenType::SEMICOLON) ||
            $this->parser->peekTokenIs(TokenType::EOF)
        ) {
            $this->parser->next();
        }

        return $stmt;
    }
}
