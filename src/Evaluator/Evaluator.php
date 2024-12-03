<?php

namespace Summer\West\Evaluator;

use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Ast\Node;
use Summer\West\Ast\Program;
use Summer\West\Object\Integer;
use Summer\West\Object\WestObject;

class Evaluator
{
    public static function eval(Node $node): ?WestObject
    {
        return match (true) {
            $node instanceof Program => self::evalStatements($node->statements),
            $node instanceof ExpressionStatement => self::eval($node->expression),
            $node instanceof IntegerLiteral => new Integer($node->value),
            default => null,
        };
    }

    private static function evalStatements(array $statements): ?WestObject
    {
        $result = null;

        foreach ($statements as $statement) {
            $result = self::eval($statement);
        }

        return $result;
    }
}
