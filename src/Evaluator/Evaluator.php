<?php

namespace Summer\West\Evaluator;

use Summer\West\Ast\BlockStatement;
use Summer\West\Ast\BooleanLiteral;
use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\IfExpression;
use Summer\West\Ast\InfixExpression;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Ast\Node;
use Summer\West\Ast\PrefixExpression;
use Summer\West\Ast\Program;
use Summer\West\Ast\ReturnStatement;
use Summer\West\Object\WestBoolean;
use Summer\West\Object\WestError;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestNull;
use Summer\West\Object\WestObject;
use Summer\West\Object\WestReturnValue;

class Evaluator
{
    // 定义常量以避免重复创建
    private const NULL = null;

    private const TRUE = true;

    private const FALSE = false;

    public static function eval(Node $node): ?WestObject
    {
        return match (true) {
            $node instanceof Program => self::evalProgram($node->statements),
            $node instanceof ExpressionStatement => self::eval($node->expression),
            $node instanceof IntegerLiteral => new WestInteger($node->value),
            $node instanceof BooleanLiteral => new WestBoolean($node->value ? self::TRUE : self::FALSE),
            $node instanceof PrefixExpression => self::evalPrefixExpression($node),
            $node instanceof InfixExpression => self::evalInfixExpression($node),
            $node instanceof BlockStatement => self::evalBlockStatement($node),
            $node instanceof IfExpression => self::evalIfExpression($node),
            $node instanceof ReturnStatement => self::evalReturnStatement($node),
            default => null,
        };
    }

    private static function evalReturnStatement(ReturnStatement $node): ?WestObject
    {
        $value = self::eval($node->returnValue);

        return new WestReturnValue($value);
    }

    private static function evalProgram(array $statements): ?WestObject
    {
        $result = null;

        foreach ($statements as $statement) {
            $result = self::eval($statement);

            if ($result instanceof WestReturnValue) {
                return $result->value;
            }
        }

        return $result;
    }

    private static function evalBlockStatement(BlockStatement $block): ?WestObject
    {
        $result = null;

        foreach ($block->statements as $statement) {
            $result = self::eval($statement);

            // 检查返回值对象
            if ($result !== null && $result instanceof WestReturnValue) {
                return $result;
            }
        }

        return $result;
    }

    private static function evalPrefixExpression(PrefixExpression $node): ?WestObject
    {
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

    private static function evalInfixExpression(InfixExpression $node): ?WestObject
    {
        $left = self::eval($node->left);
        $right = self::eval($node->right);

        if ($left === null || $right === null) {
            return self::NULL;
        }

        return match (true) {
            $left instanceof WestInteger && $right instanceof WestInteger => self::evalIntegerInfixExpression($node->operator, $left, $right),
            $node->operator === '==' => new WestBoolean($left == $right),
            $node->operator === '!=' => new WestBoolean($left != $right),
            $left::class !== $right::class => new WestError("type mismatch: {$left->type()} {$node->operator} {$right->type()}"),
            default => new WestError("unknown operator: {$left->type()} {$node->operator} {$right->type()}"),
        };
    }

    private static function evalIntegerInfixExpression(string $operator, ?WestObject $left, ?WestObject $right): ?WestObject
    {
        if ($left instanceof WestInteger && $right instanceof WestInteger) {
            $leftVal = $left->value;
            $rightVal = $right->value;

            return match ($operator) {
                '+' => new WestInteger($leftVal + $rightVal),
                '-' => new WestInteger($leftVal - $rightVal),
                '*' => new WestInteger($leftVal * $rightVal),
                '/' => new WestInteger($leftVal / $rightVal),
                '<' => new WestBoolean($leftVal < $rightVal),
                '>' => new WestBoolean($leftVal > $rightVal),
                '==' => new WestBoolean($leftVal == $rightVal),
                '!=' => new WestBoolean($leftVal != $rightVal),
                default => self::NULL,
            };
        }

        return self::NULL;
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

    private static function evalIfExpression(IfExpression $node): ?WestObject
    {
        $condition = self::eval($node->condition);

        if (self::isTruthy($condition)) {
            return self::eval($node->consequence);
        } elseif ($node->alternative !== null) {
            return self::eval($node->alternative);
        } else {
            return self::NULL;
        }
    }

    private static function isTruthy(?WestObject $obj): bool
    {
        return match (true) {
            $obj instanceof WestNull => false,
            $obj instanceof WestBoolean && ! $obj->value => false,
            default => true,
        };
    }
}
