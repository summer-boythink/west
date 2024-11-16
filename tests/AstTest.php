<?php

use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
use Summer\West\Ast\Program;
use Summer\West\Token\Token;
use Summer\West\Token\TokenType;

it('can represent a let statement program as a string', function () {
    // Create the LetStatement with appropriate tokens and identifiers
    $letStatement = new LetStatement(
        new Token(TokenType::LET, 'let'),
        new Identifier(new Token(TokenType::IDENT, 'myVar'), 'myVar'),
        new Identifier(new Token(TokenType::IDENT, 'anotherVar'), 'anotherVar')
    );

    // Create a Program and manually add the LetStatement to the statements array
    $program = new Program;
    $program->statements[] = $letStatement;

    // Assert that the string representation is correct
    expect((string) $program)->toBe('let myVar = anotherVar;');
});
