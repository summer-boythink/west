<?php

namespace Summer\West\Parser\Statement;

use Summer\West\Ast\ReturnStatement;
use Summer\West\Parser\Parser;
use Summer\West\Parser\PrecedenceLevel;
use Summer\West\Token\TokenType;

class ReturnStatementParser implements IStatement
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): ?ReturnStatement
    {
        // Get the 'return' token
        $returnToken = $this->parser->getCurToken();

        // Move to the next token after 'return'
        $this->parser->next();

        // Here we could parse an expression, but for now, we just skip until the semicolon
        // (Placeholder for expression parsing)
        $returnValue = $this->parser->parseExpression(PrecedenceLevel::LOWEST);

        if (
            $this->parser->peekTokenIs(TokenType::SEMICOLON) ||
            $this->parser->peekTokenIs(TokenType::EOF)
        ) {
            $this->parser->next();
        }

        // Return the statement
        return new ReturnStatement($returnToken, $returnValue);
    }
}
