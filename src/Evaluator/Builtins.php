<?php

namespace Summer\West\Evaluator;

use Summer\West\Object\Builtin;
use Summer\West\Object\ObjectType;
use Summer\West\Object\WestArray;
use Summer\West\Object\WestError;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestNull;
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
            'first' => new Builtin([self::class, 'firstBuiltin']),
            'last' => new Builtin([self::class, 'lastBuiltin']),
            'pop' => new Builtin([self::class, 'popBuiltin']),
            'push' => new Builtin([self::class, 'pushBuiltin']),
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
     * 支持字符串和数组
     */
    public static function lenBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) !== 1) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=1', count($args)));
        }

        $arg = $args[0];
        switch ($arg->type()) {
            case ObjectType::STRING_OBJ:
                /** @var WestString $arg */
                return new WestInteger(strlen($arg->value));
            case ObjectType::ARRAY_OBJ:
                /** @var WestArray $arg */
                return new WestInteger(count($arg->elements));
            default:
                return new WestError(sprintf('argument to `len` not supported, got %s', $arg->type()));
        }
    }

    /**
     * 内置函数 print 的实现
     */
    public static function echoBuiltin(WestObject ...$args): WestObject
    {
        $output = '';
        foreach ($args as $arg) {
            $output .= $arg->inspect();
        }

        // 使用正则表达式查找并直接处理\r和\n
        $output = preg_replace('/\\\\r/', "\r", $output);
        $output = preg_replace('/\\\\n/', "\n", $output);

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
            $output .= $arg->inspect();
        }

        echo $output.PHP_EOL;

        return new WestVoid; // 返回一个空的 WestVoid 对象，表示没有返回值
    }

    /**
     * 内置函数 first 的实现
     * 返回数组的第一个元素，如果为空数组则返回 NULL
     */
    public static function firstBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) !== 1) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=1', count($args)));
        }

        $arg = $args[0];
        if ($arg->type() !== ObjectType::ARRAY_OBJ) {
            return new WestError(sprintf('argument to `first` must be ARRAY, got %s', $arg->type()));
        }

        /** @var WestArray $arg */
        if (count($arg->elements) > 0) {
            return $arg->elements[0];
        }

        return new WestNull;
    }

    /**
     * 内置函数 last 的实现
     * 返回数组的最后一个元素，如果为空数组则返回 NULL
     */
    public static function lastBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) !== 1) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=1', count($args)));
        }

        $arg = $args[0];
        if ($arg->type() !== ObjectType::ARRAY_OBJ) {
            return new WestError(sprintf('argument to `last` must be ARRAY, got %s', $arg->type()));
        }

        /** @var WestArray $arg */
        $length = count($arg->elements);
        if ($length > 0) {
            return $arg->elements[$length - 1];
        }

        return new WestNull;
    }

    /**
     * 内置函数 rest 的实现
     * 返回数组除了第一个元素之外的所有元素组成的新数组，如果为空或单元素数组则返回空数组，否则返回NULL
     */
    public static function popBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) !== 1) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=1', count($args)));
        }

        $arg = $args[0];
        if ($arg->type() !== ObjectType::ARRAY_OBJ) {
            return new WestError(sprintf('argument to `rest` must be ARRAY, got %s', $arg->type()));
        }

        /** @var WestArray $arg */
        $length = count($arg->elements);
        if ($length > 0) {
            $newElements = array_slice($arg->elements, 1);

            return new WestArray($newElements);
        }

        return new WestNull;
    }

    /**
     * 内置函数 push 的实现
     * 返回在数组末尾添加新元素后组成的新数组，不修改原数组
     */
    public static function pushBuiltin(WestObject ...$args): WestObject
    {
        if (count($args) !== 2) {
            return new WestError(sprintf('wrong number of arguments. got=%d, want=2', count($args)));
        }

        $array = $args[0];
        $element = $args[1];

        if ($array->type() !== ObjectType::ARRAY_OBJ) {
            return new WestError(sprintf('argument to `push` must be ARRAY, got %s', $array->type()));
        }

        /** @var WestArray $array */
        $newElements = [...$array->elements, $element];

        return new WestArray($newElements);
    }
}
