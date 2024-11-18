<?php

namespace Summer\West\Token;

class Token
{
    public TokenType $type;

    public string $literal;

    public function __construct(TokenType $type, string $literal)
    {
        $this->type = $type;
        $this->literal = $literal;
    }

    // Getter for token type
    public function getType(): TokenType
    {
        return $this->type;
    }

    // Getter for token literal
    public function getLiteral(): string
    {
        return $this->literal;
    }
}
