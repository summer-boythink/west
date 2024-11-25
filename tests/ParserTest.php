<?php

namespace Tests;

use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\Identifier;
use Summer\West\Ast\IntegerLiteral;
use Summer\West\Ast\LetStatement;
use Summer\West\Ast\PrefixExpression;
use Summer\West\Ast\ReturnStatement;
use Summer\West\Lexer\Lexer;
use Summer\West\Parser\Parser;

it('parses let statements correctly', function () {
    $input = <<<'EOT'
let x = 5;
let y = 10;
let foobar = 838383;
EOT;

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();
    checkParserErrors($parser);

    expect($program)->not->toBeNull();
    expect(count($program->statements))->toBe(3);

    $tests = [
        ['expectedIdentifier' => 'x'],
        ['expectedIdentifier' => 'y'],
        ['expectedIdentifier' => 'foobar'],
    ];

    foreach ($tests as $i => $test) {
        $stmt = $program->statements[$i];
        expect(testLetStatement($stmt, $test['expectedIdentifier']))->toBeTrue();
    }
});

it('parses return statements correctly', function () {
    // Define the input string with return statements
    $input = <<<'EOT'
return 5;
return 10;
return 993 322;
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

    /** @var Identifier $expression */
    $expression = $stmt->expression;
    expect($expression)->toBeInstanceOf(Identifier::class);
    expect($expression->value)->toBe('foobar');
    expect($expression->tokenLiteral())->toBe('foobar');
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
        ['input' => '!5;', 'operator' => '!', 'integerValue' => 5],
        ['input' => '-15;', 'operator' => '-', 'integerValue' => 15],
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
        /** @var IntegerLiteral $right */
        $right = $expression->right;
        expect($right)->toBeInstanceOf(IntegerLiteral::class);
        expect($right->value)->toBe($test['integerValue']);
        expect($right->tokenLiteral())->toBe((string) $test['integerValue']);
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

function testLetStatement(LetStatement $stmt, string $name): bool
{
    expect($stmt)->toBeInstanceOf(LetStatement::class);
    expect($stmt->tokenLiteral())->toBe('let');
    expect($stmt->name->value)->toBe($name);
    expect($stmt->name->tokenLiteral())->toBe($name);

    return true;
}
