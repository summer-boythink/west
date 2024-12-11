<?php

namespace Tests;

use Exception;
use Summer\West\Ast\ArrayLiteral;
use Summer\West\Ast\BooleanLiteral;
use Summer\West\Ast\CallExpression;
use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\FunctionLiteral;
use Summer\West\Ast\Identifier;
use Summer\West\Ast\IndexExpression;
use Summer\West\Ast\InfixExpression;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Ast\LetStatement;
use Summer\West\Ast\PrefixExpression;
use Summer\West\Ast\ReturnStatement;
use Summer\West\Ast\StringLiteral;
use Summer\West\Lexer\Lexer;
use Summer\West\Parser\Parser;

it('parses let statements correctly', function () {
    $input = <<<'EOT'
let x = 5;
let y = true;
let foobar = y;
EOT;

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    expect($program)->not->toBeNull();
    expect(count($program->statements))->toBe(3);

    $tests = [
        ['expectedIdentifier' => 'x', 'expectedValue' => 5],
        ['expectedIdentifier' => 'y', 'expectedValue' => true],
        ['expectedIdentifier' => 'foobar', 'expectedValue' => 'y'],
    ];

    foreach ($tests as $i => $test) {
        /** @var LetStatement $stmt */
        $stmt = $program->statements[$i];
        testLetStatement($stmt, $test['expectedIdentifier']);
        $value = $stmt->value;
        testLiteralExpression($value, $test['expectedValue']);
    }
});

it('parses return statements correctly', function () {
    // Define the input string with return statements
    $input = <<<'EOT'
return 5;
return 10;
return true;
EOT;

    // Initialize the lexer and parser
    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    // Parse the program
    $program = $parser->parseProgram();

    // Check for parser errors
    expect($parser->getErrors())->toBeEmpty();

    // Verify that the program contains 3 statements
    expect(count($program->statements))->toBe(3);

    // Loop through each statement to check that it's a ReturnStatement
    foreach ($program->statements as $stmt) {
        /** @var ReturnStatement $stmt */
        expect($stmt)->toBeInstanceOf(ReturnStatement::class);
        expect($stmt->tokenLiteral())->toBe('return');
    }
});

it('parses identifier expressions correctly', function () {
    $input = 'foobar;';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 检查 program 的 statements 数量是否正确
    expect($program->statements)->toHaveCount(1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 验证 Identifier
    testIdentifier($stmt->expression, 'foobar');
});

it('parses string literal expressions correctly', function () {
    $input = '"hello world";';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);
    // 确保 Program 有一个语句
    expect($program->statements)->toHaveCount(1);

    // 获取第一个语句并检查其类型
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 获取表达式并检查其类型
    /** @var StringLiteral $expression */
    $expression = $stmt->expression;
    expect($expression)->toBeInstanceOf(StringLiteral::class);

    // 验证整数值和 TokenLiteral
    expect($expression->value)->toBe('hello world');
    expect($expression->tokenLiteral())->toBe('hello world');
});

it('parses integer literal expressions correctly', function () {
    $input = '5;';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 确保 Program 有一个语句
    expect($program->statements)->toHaveCount(1);

    // 获取第一个语句并检查其类型
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 获取表达式并检查其类型
    /** @var IntegerLiteral $expression */
    $expression = $stmt->expression;
    expect($expression)->toBeInstanceOf(IntegerLiteral::class);

    // 验证整数值和 TokenLiteral
    expect($expression->value)->toBe(5);
    expect($expression->tokenLiteral())->toBe('5');
});

it('parses prefix expressions correctly', function () {
    $prefixTests = [
        ['input' => '!5;', 'operator' => '!', 'value' => 5],
        ['input' => '-15;', 'operator' => '-', 'value' => 15],
        ['input' => '!true;', 'operator' => '!', 'value' => true],
        ['input' => '!false;', 'operator' => '!', 'value' => false],
    ];

    foreach ($prefixTests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        // 检查 Program 的 statements 数量是否正确
        expect($program->statements)->toHaveCount(1);

        // 获取第一个语句并检查其类型
        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];
        expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

        // 获取表达式并检查其类型
        /** @var PrefixExpression $expression */
        $expression = $stmt->expression;
        expect($expression)->toBeInstanceOf(PrefixExpression::class);

        // 验证操作符
        expect($expression->operator)->toBe($test['operator']);

        // 验证右侧表达式的值
        testLiteralExpression($expression->right, $test['value']);
    }
});

it('parses infix expressions correctly', function () {
    $infixTests = [
        ['input' => '5 + 5;', 'leftValue' => 5, 'operator' => '+', 'rightValue' => 5],
        ['input' => '5 - 5;', 'leftValue' => 5, 'operator' => '-', 'rightValue' => 5],
        ['input' => '5 * 5;', 'leftValue' => 5, 'operator' => '*', 'rightValue' => 5],
        ['input' => '5 / 5;', 'leftValue' => 5, 'operator' => '/', 'rightValue' => 5],
        ['input' => '5 > 5;', 'leftValue' => 5, 'operator' => '>', 'rightValue' => 5],
        ['input' => '5 < 5;', 'leftValue' => 5, 'operator' => '<', 'rightValue' => 5],
        ['input' => '5 == 5;', 'leftValue' => 5, 'operator' => '==', 'rightValue' => 5],
        ['input' => '5 != 5;', 'leftValue' => 5, 'operator' => '!=', 'rightValue' => 5],
        ['input' => 'true == true;', 'leftValue' => true, 'operator' => '==', 'rightValue' => true],
        ['input' => 'true != false;', 'leftValue' => true, 'operator' => '!=', 'rightValue' => false],
        ['input' => 'false == false;', 'leftValue' => false, 'operator' => '==', 'rightValue' => false],
        ['input' => 'a + b;', 'leftValue' => 'a', 'operator' => '+', 'rightValue' => 'b'],
    ];

    foreach ($infixTests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        // 检查 Program 的 statements 数量是否正确
        expect($program->statements)->toHaveCount(1);

        // 获取第一个语句并检查其类型
        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];
        expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

        // 获取表达式并检查其类型
        /** @var InfixExpression $expression */
        $expression = $stmt->expression;
        expect($expression)->toBeInstanceOf(InfixExpression::class);

        // 使用通用的测试函数验证中缀表达式
        testInfixExpression($expression, $test['leftValue'], $test['operator'], $test['rightValue']);
    }
});

it('parses expressions with correct operator precedence', function () {
    $tests = [
        ['input' => '-a * b', 'expected' => '((-a) * b)'],
        ['input' => '!-a', 'expected' => '(!(-a))'],
        ['input' => 'a + b + c', 'expected' => '((a + b) + c)'],
        ['input' => 'a + b - c', 'expected' => '((a + b) - c)'],
        ['input' => 'a * b * c', 'expected' => '((a * b) * c)'],
        ['input' => 'a * b / c', 'expected' => '((a * b) / c)'],
        ['input' => 'a + b / c', 'expected' => '(a + (b / c))'],
        ['input' => 'a + b * c + d / e - f', 'expected' => '(((a + (b * c)) + (d / e)) - f)'],
        ['input' => '3 + 4; -5 * 5', 'expected' => '(3 + 4)((-5) * 5)'],
        ['input' => '5 > 4 == 3 < 4', 'expected' => '((5 > 4) == (3 < 4))'],
        ['input' => '5 < 4 != 3 > 4', 'expected' => '((5 < 4) != (3 > 4))'],
        ['input' => '3 + 4 * 5 == 3 * 1 + 4 * 5', 'expected' => '((3 + (4 * 5)) == ((3 * 1) + (4 * 5)))'],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        expect($actual)->toBe($test['expected']);
    }
});

it('parses expressions with correct operator precedence including booleans', function () {
    $tests = [
        ['input' => 'true', 'expected' => 'true'],
        ['input' => 'false', 'expected' => 'false'],
        ['input' => '3 > 5 == false', 'expected' => '((3 > 5) == false)'],
        ['input' => '3 < 5 == true', 'expected' => '((3 < 5) == true)'],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        expect($actual)->toBe($test['expected']);
    }
});

it('parses expressions with correct operator precedence including LPAREN', function () {
    $tests = [
        ['input' => '1 + (2 + 3) + 4', 'expected' => '((1 + (2 + 3)) + 4)'],
        ['input' => '(5 + 5) * 2', 'expected' => '((5 + 5) * 2)'],
        ['input' => '2 / (5 + 5)', 'expected' => '(2 / (5 + 5))'],
        ['input' => '-(5 + 5)', 'expected' => '(-(5 + 5))'],
        ['input' => '!(true == true)', 'expected' => '(!(true == true))'],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        expect($actual)->toBe($test['expected']);
    }
});

it('parses if expressions correctly with or without else', function () {
    $tests = [
        [
            'input' => 'if (x < y) { x }',
            'expected' => 'if ((x < y)) x',
        ],
        [
            'input' => 'if (x < y) { x } else { y }',
            'expected' => 'if ((x < y)) x else y',
        ],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        expect($actual)->toBe($test['expected']);
    }
});

it('parses function literals correctly', function () {
    $input = 'fn(x, y) { x + y; }';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 检查 Program 的 statements 数量是否正确
    expect($program->statements)->toHaveCount(1);

    // 验证第一个语句的类型
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 验证函数字面量
    /** @var FunctionLiteral $function */
    $function = $stmt->expression;
    expect($function)->toBeInstanceOf(FunctionLiteral::class);

    // 验证参数列表
    expect($function->parameters)->toHaveCount(2);
    testLiteralExpression($function->parameters[0], 'x');
    testLiteralExpression($function->parameters[1], 'y');

    // 验证函数体
    expect($function->body->statements)->toHaveCount(1);

    /** @var ExpressionStatement $bodyStmt */
    $bodyStmt = $function->body->statements[0];
    expect($bodyStmt)->toBeInstanceOf(ExpressionStatement::class);

    // 验证函数体中的表达式
    testInfixExpression($bodyStmt->expression, 'x', '+', 'y');
});

it('parses function parameters correctly', function () {
    $tests = [
        ['input' => 'fn() {};', 'expectedParameters' => []],
        ['input' => 'fn(x) {};', 'expectedParameters' => ['x']],
        ['input' => 'fn(x, y, z) {};', 'expectedParameters' => ['x', 'y', 'z']],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];
        expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

        /** @var FunctionLiteral $function */
        $function = $stmt->expression;
        expect($function)->toBeInstanceOf(FunctionLiteral::class);

        // 验证参数数量和名称
        expect($function->parameters)->toHaveCount(count($test['expectedParameters']));

        foreach ($function->parameters as $index => $parameter) {
            testIdentifier($parameter, $test['expectedParameters'][$index]);
        }
    }
});

it('parses call expressions correctly', function () {
    $input = 'add(1, 2 * 3, 4 + 5);';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 检查 Program 的 statements 数量是否正确
    expect($program->statements)->toHaveCount(1);

    // 检查第一个语句是否是 ExpressionStatement
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 检查表达式是否是 CallExpression
    /** @var CallExpression $exp */
    $exp = $stmt->expression;
    expect($exp)->toBeInstanceOf(CallExpression::class);

    // 验证函数部分是否是正确的标识符
    testIdentifier($exp->function, 'add');

    // 检查参数数量是否正确
    expect($exp->arguments)->toHaveCount(3);

    // 验证每个参数
    testLiteralExpression($exp->arguments[0], 1);
    testInfixExpression($exp->arguments[1], 2, '*', 3);
    testInfixExpression($exp->arguments[2], 4, '+', 5);
});

it('parses expressions with correct operator precedence including call expressions', function () {
    $tests = [
        ['input' => 'a + add(b * c) + d', 'expected' => '((a + add((b * c))) + d)'],
        ['input' => 'add(a, b, 1, 2 * 3, 4 + 5, add(6, 7 * 8))', 'expected' => 'add(a, b, 1, (2 * 3), (4 + 5), add(6, (7 * 8)))'],
        ['input' => 'add(a + b + c * d / f + g)', 'expected' => 'add((((a + b) + ((c * d) / f)) + g))'],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        // 验证解析的输出是否符合预期
        expect($actual)->toBe($test['expected']);
    }
});

it('parses array literals correctly', function () {
    $input = '[1, 2 * 2, 3 + 3]';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 检查 Program 的 statements 数量是否正确
    expect($program->statements)->toHaveCount(1);

    // 检查第一个语句是否是 ExpressionStatement
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 检查表达式是否是 ArrayLiteral
    /** @var ArrayLiteral $array */
    $array = $stmt->expression;
    expect($array)->toBeInstanceOf(ArrayLiteral::class);

    // 验证元素数量
    expect($array->elements)->toHaveCount(3);

    // 验证每个元素
    testLiteralExpression($array->elements[0], 1);
    testInfixExpression($array->elements[1], 2, '*', 2);
    testInfixExpression($array->elements[2], 3, '+', 3);
});

it('parses index expressions correctly', function () {
    $input = 'myArray[1 + 1]';

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    // 检查 Program 的 statements 数量是否正确
    expect($program->statements)->toHaveCount(1);

    // 检查第一个语句是否是 ExpressionStatement
    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];
    expect($stmt)->toBeInstanceOf(ExpressionStatement::class);

    // 检查表达式是否是 IndexExpression
    /** @var IndexExpression $indexExp */
    $indexExp = $stmt->expression;
    expect($indexExp)->toBeInstanceOf(IndexExpression::class);

    // 验证左侧表达式是否为标识符
    testIdentifier($indexExp->left, 'myArray');

    // 验证索引表达式
    testInfixExpression($indexExp->index, 1, '+', 1);
});

it('parses operator precedence with index expressions correctly', function () {
    $tests = [
        [
            'input' => 'a * [1, 2, 3, 4][b * c] * d',
            'expected' => '((a * ([1, 2, 3, 4][(b * c)])) * d)',
        ],
        [
            'input' => 'add(a * b[2], b[1], 2 * [1, 2][1])',
            'expected' => 'add((a * (b[2])), (b[1]), (2 * ([1, 2][1])))',
        ],
    ];

    foreach ($tests as $test) {
        $lexer = new Lexer($test['input']);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();
        checkParserErrors($parser);

        $actual = (string) $program;

        // 验证解析的输出是否符合预期
        expect($actual)->toBe($test['expected']);
    }
});

function checkParserErrors(Parser $parser): void
{
    $errors = $parser->getErrors();
    if (empty($errors)) {
        return;
    }

    foreach ($errors as $error) {
        echo "Parser error: {$error}\n";
    }
}

function testIntegerLiteral($expression, int $expectedValue): void
{
    expect($expression)->toBeInstanceOf(IntegerLiteral::class);
    expect($expression->value)->toBe($expectedValue);
    expect($expression->tokenLiteral())->toBe((string) $expectedValue);
}

function testIdentifier($expression, string $expectedValue): void
{
    expect($expression)->toBeInstanceOf(Identifier::class);

    /** @var Identifier $expression */
    expect($expression->value)->toBe($expectedValue);
    expect($expression->tokenLiteral())->toBe($expectedValue);
}

function testLiteralExpression($expression, $expected): void
{
    match (true) {
        is_int($expected) => testIntegerLiteral($expression, $expected),
        is_bool($expected) => testBooleanLiteral($expression, $expected),
        is_string($expected) => testIdentifier($expression, $expected),
        default => throw new Exception(
            'Unsupported type for literal expression: '.get_debug_type($expected)
        ),
    };
}

function testInfixExpression($expression, $left, string $operator, $right): void
{
    expect($expression)->toBeInstanceOf(InfixExpression::class);

    /** @var InfixExpression $expression */
    testLiteralExpression($expression->left, $left);
    expect($expression->operator)->toBe($operator);
    testLiteralExpression($expression->right, $right);
}

function testBooleanLiteral($expression, bool $expectedValue): void
{
    expect($expression)->toBeInstanceOf(BooleanLiteral::class);

    /** @var BooleanLiteral $expression */
    expect($expression->value)->toBe($expectedValue);
    expect($expression->tokenLiteral())->toBe($expectedValue ? 'true' : 'false');
}

function testLetStatement(LetStatement $stmt, string $name): void
{
    expect($stmt)->toBeInstanceOf(LetStatement::class);
    expect($stmt->tokenLiteral())->toBe('let');
    expect($stmt->name->value)->toBe($name);
    expect($stmt->name->tokenLiteral())->toBe($name);
}
