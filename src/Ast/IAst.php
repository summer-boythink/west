<?php

namespace Summer\West\Ast;

interface Node
{
    public function tokenLiteral(): string;

    public function __toString(): string;
}

interface Statement extends Node
{
    public function statementNode();
}

interface Expression extends Node
{
    public function expressionNode();
}
