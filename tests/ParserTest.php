<?php

namespace Tests;

use Summer\West\Ast\ExpressionStatement;
use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
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
