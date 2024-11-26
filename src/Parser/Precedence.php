<?php

namespace Summer\West\Parser;

use Summer\West\Token\TokenType;

class Precedence
{
    private static array $precedenceMap = [
        TokenType::EQ->value => PrecedenceLevel::EQUALS,
        TokenType::NOT_EQ->value => PrecedenceLevel::EQUALS,
        TokenType::LT->value => PrecedenceLevel::LESSGREATER,
        TokenType::GT->value => PrecedenceLevel::LESSGREATER,
        TokenType::PLUS->value => PrecedenceLevel::SUM,
        TokenType::MINUS->value => PrecedenceLevel::SUM,
        TokenType::SLASH->value => PrecedenceLevel::PRODUCT,
        TokenType::ASTERISK->value => PrecedenceLevel::PRODUCT,
    ];

    public static function getPrecedence(TokenType $type): PrecedenceLevel
    {
        return self::$precedenceMap[$type->value] ?? PrecedenceLevel::LOWEST;
    }
}
