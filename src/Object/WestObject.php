<?php

namespace Summer\West\Object;

use Summer\West\Ast\BlockStatement;
use Summer\West\Ast\Identifier;

abstract class ObjectType
{
    public const NULL_OBJ = 'NULL';

    public const ERROR_OBJ = 'ERROR';

    public const INTEGER_OBJ = 'INTEGER';

    public const STRING_OBJ = 'STRING';

    public const BOOLEAN_OBJ = 'BOOLEAN';

    public const RETURN_VALUE_OBJ = 'RETURN_VALUE';

    public const FUNCTION_OBJ = 'FUNCTION';
}

interface WestObject
{
    public function type(): string;

    public function inspect(): string;
}

class WestInteger implements WestObject
{
    public int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return ObjectType::INTEGER_OBJ;
    }

    public function inspect(): string
    {
        return (string) $this->value;
    }
}

class WestString implements WestObject
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return ObjectType::STRING_OBJ;
    }

    public function inspect(): string
    {
        return (string) $this->value;
    }
}

class WestBoolean implements WestObject
{
    public bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return ObjectType::BOOLEAN_OBJ;
    }

    public function inspect(): string
    {
        return $this->value ? 'true' : 'false';
    }
}

class WestNull implements WestObject
{
    public function type(): string
    {
        return ObjectType::NULL_OBJ;
    }

    public function inspect(): string
    {
        return 'null';
    }
}

class WestReturnValue implements WestObject
{
    public object $value;

    public function __construct(object $value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return ObjectType::RETURN_VALUE_OBJ;
    }

    public function inspect(): string
    {
        return $this->value->inspect();
    }
}

class WestError implements WestObject
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function type(): string
    {
        return ObjectType::ERROR_OBJ;
    }

    public function inspect(): string
    {
        return 'ERROR: '.$this->message;
    }
}

class WestFunction implements WestObject
{
    /**
     * @var Identifier[]
     */
    public array $parameters;

    public BlockStatement $body;

    public ?Environment $env;

    public function __construct(array $parameters, BlockStatement $body, ?Environment $env = null)
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->env = $env;
    }

    public function type(): string
    {
        return ObjectType::FUNCTION_OBJ;
    }

    public function inspect(): string
    {
        $params = array_map(fn (Identifier $p) => $p->__toString(), $this->parameters);

        $out = 'fn('.implode(', ', $params).') {'.PHP_EOL;
        $out .= $this->body->__toString().PHP_EOL;
        $out .= '}';

        return $out;
    }
}
