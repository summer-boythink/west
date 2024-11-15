<?php

namespace Summer\West\Token;

class Token
{
    public string $type;

    public string $literal;

    public function __construct(string $type, string $literal)
    {
        $this->type = $type;
        $this->literal = $literal;
    }

    // Getter for token type
    public function getType(): string
    {
        return $this->type;
    }

    // Getter for token literal
    public function getLiteral(): string
    {
        return $this->literal;
    }
}
