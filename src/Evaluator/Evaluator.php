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
use Summer\West\Object\ObjectType;
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

    private static function isError(?WestObject $obj): bool
    {
        return $obj instanceof WestError;
    }

    public static function eval(Node $node): ?WestObject
    {
        $result = match (true) {
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

        // 检查是否为错误对象
        if (self::isError($result)) {
            return $result;  // 提前返回错误对象，跳出方法
        }

        return $result;
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
            } elseif ($result instanceof WestError) {
                return $result;
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
            if ($result !== null) {
                if (
                    $result->type() == ObjectType::RETURN_VALUE_OBJ
                    || $result->type() == ObjectType::ERROR_OBJ
                ) {
                    return $result;
                }
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
            default => self::newError('unknown operator: %s%s', $operator, $right->type()),
        };
    }

    private static function evalInfixExpression(InfixExpression $node): ?WestObject
    {
        $left = self::eval($node->left);
        $right = self::eval($node->right);

        if ($left === null || $right === null) {
            return self::NULL;
        }

        // 如果类型不匹配，返回类型错误
        if ($left::class !== $right::class) {
            return self::newError('type mismatch: %s %s %s', $left->type(), $node->operator, $right->type());
        }

        return match (true) {
            $left instanceof WestInteger && $right instanceof WestInteger => self::evalIntegerInfixExpression($node->operator, $left, $right),
            $node->operator === '==' => new WestBoolean($left == $right),
            $node->operator === '!=' => new WestBoolean($left != $right),
            default => self::newError('unknown operator: %s %s %s', $left->type(), $node->operator, $right->type()),
        };
    }

    private static function evalIntegerInfixExpression(string $operator, ?WestObject $left, ?WestObject $right): ?WestObject
    {
        // 确保左右操作数都是整数类型
        if ($left instanceof WestInteger && $right instanceof WestInteger) {
            $leftVal = $left->value;
            $rightVal = $right->value;

            return match ($operator) {
                '+' => new WestInteger($leftVal + $rightVal),
                '-' => new WestInteger($leftVal - $rightVal),
                '*' => new WestInteger($leftVal * $rightVal),
                '/' => $rightVal !== 0 ? new WestInteger($leftVal / $rightVal) : self::newError('division by zero'),
                '<' => new WestBoolean($leftVal < $rightVal),
                '>' => new WestBoolean($leftVal > $rightVal),
                '==' => new WestBoolean($leftVal == $rightVal),
                '!=' => new WestBoolean($leftVal != $rightVal),
                default => self::newError('unknown operator: %s %s %s', $left->type(), $operator, $right->type()),
            };
        }

        // 如果操作数不是整数类型，返回类型不匹配的错误
        return self::newError('type mismatch: %s %s %s', $left?->type() ?? 'null', $operator, $right?->type() ?? 'null');
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
        // 检查右操作数是否为整数类型
        if ($right instanceof WestInteger) {
            return new WestInteger(-$right->value);
        }

        // 如果操作数不是整数类型，返回明确的错误对象
        return self::newError('unknown operator: -%s', $right?->type() ?? 'null');
    }

    private static function evalIfExpression(IfExpression $node): ?WestObject
    {
        $condition = self::eval($node->condition);

        if (self::isError($condition)) {
            return $condition;
        } elseif (self::isTruthy($condition)) {
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

    /**
     * 创建错误对象
     *
     * @param  string  $format  错误格式
     * @param  mixed  ...$args  错误参数
     */
    private static function newError(string $format, ...$args): WestError
    {
        return new WestError(sprintf($format, ...$args));
    }
}
