<?php

namespace Summer\West\Ast;

use Summer\West\Token\Token;

class Program
{
    /** @var Statement[] */
    public array $statements = [];

    public function __construct(array $statements = [])
    {
        $this->statements = $statements;
    }

    public function tokenLiteral(): string
    {
        return $this->statements[0]->tokenLiteral() ?? '';
    }

    public function __toString(): string
    {
        return implode('', array_map(fn ($s) => $s->__toString(), $this->statements));
    }
}

class LetStatement extends BaseNode implements Statement
{
    public Identifier $name;

    public ?Expression $value;

    public function __construct(Token $token, Identifier $name, ?Expression $value)
    {
        parent::__construct($token);
        $this->name = $name;
        $this->value = $value;
    }

    public function statementNode() {}

    public function __toString(): string
    {
        return $this->tokenLiteral().' '.$this->name->__toString().' = '.$this->value->__toString().';';
    }
}

class ReturnStatement extends BaseNode implements Statement
{
    public ?Expression $returnValue;

    public function __construct(Token $token, ?Expression $returnValue = null)
    {
        parent::__construct($token);
        $this->returnValue = $returnValue;
    }

    public function statementNode() {}

    public function __toString(): string
    {
        return $this->tokenLiteral().' '.($this->returnValue ? $this->returnValue->__toString() : '').';';
    }
}

class ExpressionStatement extends BaseNode implements Statement
{
    public ?Expression $expression;

    public function __construct(Token $token, ?Expression $expression)
    {
        parent::__construct($token);
        $this->expression = $expression;
    }

    public function statementNode() {}

    public function __toString(): string
    {
        return $this->expression->__toString();
    }
}

class BlockStatement extends BaseNode implements Statement
{
    public array $statements = [];

    public function __construct(Token $token, array $statements = [])
    {
        parent::__construct($token);
        $this->statements = $statements;
    }

    public function statementNode() {}

    public function __toString(): string
    {
        return implode('', array_map(fn ($s) => $s->__toString(), $this->statements));
    }
}

class Identifier extends BaseNode implements Expression
{
    public string $value;

    public function __construct(Token $token, string $value)
    {
        parent::__construct($token);
        $this->value = $value;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        return $this->value;
    }
}

class BooleanLiteral extends BaseNode implements Expression
{
    public bool $value;

    public function __construct(Token $token, bool $value)
    {
        parent::__construct($token);
        $this->value = $value;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        return $this->tokenLiteral();
    }
}

class IntegerLiteral extends BaseNode implements Expression
{
    public ?int $value;

    public function __construct(Token $token, ?int $value)
    {
        parent::__construct($token);
        $this->value = $value;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        return $this->tokenLiteral();
    }
}

class PrefixExpression extends BaseNode implements Expression
{
    public string $operator;

    public Expression $right;

    public function __construct(Token $token, string $operator, Expression $right)
    {
        parent::__construct($token);
        $this->operator = $operator;
        $this->right = $right;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        return '('.$this->operator.$this->right->__toString().')';
    }
}

class InfixExpression extends BaseNode implements Expression
{
    public Expression $left;

    public string $operator;

    public Expression $right;

    public function __construct(Token $token, Expression $left, string $operator, Expression $right)
    {
        parent::__construct($token);
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        return '('.$this->left->__toString().' '.$this->operator.' '.$this->right->__toString().')';
    }
}

class IfExpression extends BaseNode implements Expression
{
    public Expression $condition;

    public BlockStatement $consequence;

    public ?BlockStatement $alternative;

    public function __construct(Token $token, Expression $condition, BlockStatement $consequence, ?BlockStatement $alternative = null)
    {
        parent::__construct($token);
        $this->condition = $condition;
        $this->consequence = $consequence;
        $this->alternative = $alternative;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        $out = 'if ('.$this->condition->__toString().') '.$this->consequence->__toString();
        if ($this->alternative !== null) {
            $out .= ' else '.$this->alternative->__toString();
        }

        return $out;
    }
}

class FunctionLiteral extends BaseNode implements Expression
{
    /** @var Identifier[] */
    public array $parameters;

    public BlockStatement $body;

    public function __construct(Token $token, array $parameters, BlockStatement $body)
    {
        parent::__construct($token);
        $this->parameters = $parameters;
        $this->body = $body;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        $params = implode(', ', array_map(fn ($p) => $p->__toString(), $this->parameters));

        return $this->tokenLiteral().'('.$params.') '.$this->body->__toString();
    }
}

class CallExpression extends BaseNode implements Expression
{
    public Expression $function;

    /** @var Expression[] */
    public array $arguments;

    public function __construct(Token $token, Expression $function, array $arguments)
    {
        parent::__construct($token);
        $this->function = $function;
        $this->arguments = $arguments;
    }

    public function expressionNode() {}

    public function __toString(): string
    {
        $args = implode(', ', array_map(fn ($a) => $a->__toString(), $this->arguments));

        return $this->function->__toString().'('.$args.')';
    }
}
