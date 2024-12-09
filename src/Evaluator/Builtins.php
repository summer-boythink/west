<?php

namespace Summer\West\Evaluator;

use Summer\West\Object\Builtin;
use Summer\West\Object\WestError;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestObject;
use Summer\West\Object\WestString;
use Summer\West\Object\WestVoid;

/**
 * Builtins 类用于存放所有内置函数的定义和实现。
 */
class Builtins
{
    /**
     * 静态内置函数映射
     *
     * @var array<string, Builtin>
     */
    public static array $builtins = [];

    /**
     * 初始化内置函数映射
     */
    public static function initializeBuiltins(): void
    {
        self::$builtins = [
            'len' => new Builtin([self::class, 'lenBuiltin']),
            'print' => new Builtin([self::class, 'echoBuiltin']),
        ];
    }

    /**
     * 获取所有内置函数的映射
     *
     * @return array<string, Builtin>
     */
    public static function getBuiltins(): array
    {
        // 确保在第一次访问时初始化内置函数
        if (empty(self::$builtins)) {
            self::initializeBuiltins();
        }

        return self::$builtins;
    }

    /**
     * 内置函数 len 的实现
     */
    public static function lenBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) != 1) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=1', count($args)));
        }

        $arg = $args[0];
        if ($arg instanceof WestString) {
            return new WestInteger(strlen($arg->value));
        }

        return new WestError(sprintf('argument to `len` not supported, got %s', $arg->type()));
    }

    /**
     * 内置函数 print 的实现
     */
    public static function echoBuiltin(WestObject ...$args): WestObject
    {
        $output = '';
        foreach ($args as $arg) {
            $output .= $arg->value;
        }

        // 输出结果（这里我们假设直接输出到终端或日志）
        echo $output;

        return new WestVoid; // 可以返回输出长度作为示例
    }
}
