<?php

namespace Tests;

use Summer\West\Ast\LetStatement;
use Summer\West\Lexer\Lexer;

it('parses let statements correctly', function () {
    $input = <<<'EOT'
let x = 5;
let y = 10;
let foobar = 838383;
EOT;

    $lexer = new Lexer($input);
    $parser = new Parser($lexer);

    $program = $parser->parseProgram();

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

function testLetStatement($stmt, string $name): bool
{
    expect($stmt)->toBeInstanceOf(LetStatement::class);
    expect($stmt->tokenLiteral())->toBe('let');
    expect($stmt->name->value)->toBe($name);
    expect($stmt->name->tokenLiteral())->toBe($name);

    return true;
}
