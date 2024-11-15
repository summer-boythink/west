<?php

namespace Summer\West\Token;

class Tokenizer
{
    private static array $keywords = [
        'fn' => TokenType::FUNCTION,
        'let' => TokenType::LET,
        'true' => TokenType::TRUE,
        'false' => TokenType::FALSE,
        'if' => TokenType::IF,
        'else' => TokenType::ELSE,
        'return' => TokenType::RETURN,
    ];

    public static function lookupIdent(string $ident): string
    {
        if (array_key_exists($ident, self::$keywords)) {
            return self::$keywords[$ident];
        }

        return TokenType::IDENT;
    }
}
