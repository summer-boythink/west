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
            'println' => new Builtin([self::class, 'printlnBuiltin']),
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
            /**
             * @var WestObject $arg
             */
            $output .= $arg->inspect();
        }

        // 使用正则表达式查找并直接处理换行符
        $output = preg_replace('/\\\\r/', "\r", $output); // 替换 '\r' 为换行符
        $output = preg_replace('/\\\\n/', "\n", $output); // 替换 '\n' 为换行符

        // 输出处理后的结果
        echo $output;

        return new WestVoid; // 返回一个空的 WestVoid 对象，表示没有返回值
    }

    /**
     * 内置函数 println 的实现
     */
    public static function printlnBuiltin(WestObject ...$args): WestObject
    {
        $output = '';
        foreach ($args as $arg) {
            /**
             * @var WestObject $arg
             */
            $output .= $arg->inspect();
        }

        // 输出结果并换行
        echo $output.PHP_EOL;

        return new WestVoid; // 返回一个空的 WestVoid 对象，表示没有返回值
    }
}
