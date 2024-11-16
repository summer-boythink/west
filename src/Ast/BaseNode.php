<?php

namespace Summer\West\Ast;

abstract class BaseNode implements Node
{
    protected Token $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    abstract public function __toString(): string;
}
