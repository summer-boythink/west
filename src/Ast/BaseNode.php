<?php

namespace Summer\West\Ast;

use Summer\West\Token\Token;

abstract class BaseNode implements Node
{
    protected Token $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    public function tokenLiteral(): string
    {
        return $this->token->getLiteral();
    }

    abstract public function __toString(): string;
}
