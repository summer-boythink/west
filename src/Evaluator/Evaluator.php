<?php

namespace Summer\West\Evaluator;

use Summer\West\Ast\BooleanLiteral;
use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Ast\Node;
use Summer\West\Ast\PrefixExpression;
use Summer\West\Ast\Program;
use Summer\West\Object\WestBoolean;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestNull;
use Summer\West\Object\WestObject;

class Evaluator
{
    // 定义常量以避免重复创建
    private const NULL = null;

    private const TRUE = true;

    private const FALSE = false;

    public static function eval(Node $node): ?WestObject
    {
        return match (true) {
            $node instanceof Program => self::evalStatements($node->statements),
            $node instanceof ExpressionStatement => self::eval($node->expression),
            $node instanceof IntegerLiteral => new WestInteger($node->value),
            $node instanceof BooleanLiteral => new WestBoolean($node->value ? self::TRUE : self::FALSE),
            $node instanceof PrefixExpression => self::evalPrefixExpression($node),
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

    private static function evalPrefixExpression(PrefixExpression $node): ?WestObject
    {
        // 评估表达式的右侧部分
        $right = self::eval($node->right);

        return self::evalPrefixOperatorExpression($node->operator, $right);
    }

    private static function evalPrefixOperatorExpression(string $operator, ?WestObject $right): ?WestObject
    {
        return match ($operator) {
            '!' => self::evalBangOperatorExpression($right),
            '-' => self::evalMinusPrefixOperatorExpression($right),
            default => self::NULL,
        };
    }

    private static function evalBangOperatorExpression(?WestObject $right): ?WestObject
    {
        // 处理逻辑非（!）操作符
        if ($right instanceof WestBoolean) {
            return $right->value ? new WestBoolean(self::FALSE) : new WestBoolean(self::TRUE);
        }

        // 如果是空值（null），逻辑非也是 true
        if ($right instanceof WestNull) {
            return new WestBoolean(self::TRUE);
        }

        // 对于其他对象，认为它是“真”值，逻辑非返回 false
        return new WestBoolean(self::FALSE);
    }

    private static function evalMinusPrefixOperatorExpression(?WestObject $right): ?WestObject
    {
        // 处理前缀负号（-）操作符
        if ($right instanceof WestInteger) {
            return new WestInteger(-$right->value);
        }

        // 如果操作数不是整数，返回 NULL
        return self::NULL;
    }
}
