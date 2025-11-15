# West

West 是一个用于学习目的的 PHP 实现的编程语言解释器，参考学习了Monkey的设计与实现。包含完整的词法分析、语法分析、抽象语法树（AST）构建和表达式求值功能。

## 简介

West 实现了以下核心组件：

- **词法分析器（Lexer）**：将源代码转换为词法单元（Token）流
- **语法分析器（Parser）**：将词法单元流解析为抽象语法树（AST）
- **求值器（Evaluator）**：遍历 AST 并执行代码
- **对象系统（Object）**：表示运行时的各种数据类型
- **REPL**：交互式解释器环境

该项目支持变量声明、函数定义、条件表达式、数组操作等基本编程语言特性。

## 功能特性

- ✅ 变量声明和赋值（let 语句）
- ✅ 函数定义和调用
- ✅ 条件表达式（if-else）
- ✅ 算术运算（+、-、*、/）
- ✅ 布尔运算（==、!=、<、>、!）
- ✅ 数组字面量和索引访问
- ✅ 字符串支持
- ✅ 内置函数
- ✅ 交互式 REPL 模式
- ✅ 文件执行模式

## 前置安装条件

在使用 West 之前，请确保您的系统满足以下要求：

### 必需条件

- **PHP 8.3 或更高版本**
  ```bash
  # 检查 PHP 版本
  php -v
  ```

- **Composer**（PHP 依赖管理工具）
  ```bash
  # 检查 Composer 是否已安装
  composer --version
  ```

### 开发依赖（可选）

如果您需要运行测试或进行开发，还需要：

- **Pest**：PHP 测试框架（通过 Composer 安装）
- **Laravel Pint**：代码格式化工具（通过 Composer 安装）

## 安装

1. **克隆项目**

```bash
git clone git@github.com:summer-boythink/west.git
cd west
```

2. **安装依赖**

```bash
composer install
```

3. **验证安装**

```bash
composer start -- -d
```

如果看到 `>>` 提示符，说明安装成功。

## 使用方法

### 交互式 REPL 模式

启动交互式解释器，逐行输入和执行代码：

```bash
# 使用 Composer 脚本
composer start -- -d

# 或直接使用 PHP
php bin/main.php -d
```

**示例会话：**

```
>> let x = 5;
>> let y = 10;
>> x + y
15
>> let add = fn(a, b) { a + b };
>> add(3, 7)
10
>> if (x > 3) { "x is greater" } else { "x is smaller" }
x is greater
```

### 文件执行模式

创建一个 `.west` 文件并执行：

**示例文件（example.west）：**

```
let name = "West";
let greeting = fn(name) {
    "Hello, " + name + "!"
};

greeting(name)
```

**执行文件：**

```bash
# 使用 Composer 脚本
composer start example.west

# 或直接使用 PHP
php bin/main.php example.west
```

## 语言语法示例

### 变量声明

```
let x = 5;
let name = "West";
let isTrue = true;
```

### 函数定义

```
let add = fn(a, b) {
    a + b
};

let factorial = fn(n) {
    if (n == 0) {
        1
    } else {
        n * factorial(n - 1)
    }
};
```

### 数组操作

```
let arr = [1, 2, 3, 4, 5];
arr[0]  // 返回 1
let first = fn(arr) { arr[0] };
```

### 条件表达式

```
let max = fn(a, b) {
    if (a > b) {
        a
    } else {
        b
    }
};
```

## 开发命令

项目提供了以下 Composer 脚本：

```bash
# 启动 REPL
composer start -- -d

# 执行文件
composer start <filename>

# 运行测试
composer test

# 代码格式化
composer fmt

# 重新生成自动加载文件
composer dump-autoload
```

## 运行测试

项目使用 Pest 测试框架：

```bash
# 运行所有测试
composer test

# 或直接使用 Pest
./vendor/bin/pest
```

测试文件位于 `tests/` 目录：
- `LexerTest.php` - 词法分析器测试
- `ParserTest.php` - 语法分析器测试
- `AstTest.php` - AST 测试
- `EvalTest.php` - 求值器测试

## 项目结构

```
west/
├── bin/
│   └── main.php           # 入口文件
├── src/
│   ├── Ast/               # 抽象语法树
│   ├── Evaluator/         # 求值器
│   ├── Lexer/             # 词法分析器
│   ├── Object/            # 对象系统
│   ├── Parser/            # 语法分析器
│   ├── Repl/              # 交互式解释器
│   └── Token/             # 词法单元
├── tests/                 # 测试文件
├── composer.json          # 项目配置
└── phpunit.xml            # 测试配置
```

## 相关资源

- [Monkey lang](https://interpreterbook.com/)
- [Pest PHP 测试框架](https://pestphp.com/)
