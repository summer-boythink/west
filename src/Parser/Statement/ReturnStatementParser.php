<?php

namespace Summer\West\Parser\Statement;

use Summer\West\Ast\ReturnStatement;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class ReturnStatementParser implements IStatementParser
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
        $returnValue = null;

        // Skip tokens until we encounter a semicolon
        while ($this->parser->getCurToken()->type !== TokenType::SEMICOLON &&
               $this->parser->getCurToken()->type !== TokenType::EOF) {
            $this->parser->next();
        }

        // Return the statement
        return new ReturnStatement($returnToken, $returnValue);
    }
}
