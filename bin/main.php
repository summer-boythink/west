#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use Summer\West\Repl\ConsoleRepl;
use Summer\West\Repl\FileRepl;

$arguments = $argv;
array_shift($arguments);

// 检查是否包含 -d 参数
if (in_array('-d', $arguments)) {
    // 启动 REPL 模式
    $repl = new ConsoleRepl;
    $repl->start(STDIN, STDOUT);
} else {
    // 非 REPL 模式下，从文件中读取并执行
    if (count($arguments) === 0) {
        fwrite(STDERR, "Usage: php west.php [-d] [file]\n");
        exit(1);
    }

    $filename = $arguments[0];

    $repl = new FileRepl;
    $repl->start($filename);
}
