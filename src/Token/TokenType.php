<?php

namespace Summer\West\Token;

class TokenType
{
    const ILLEGAL = 'ILLEGAL';

    const EOF = 'EOF';

    // Identifiers + literals
    const IDENT = 'IDENT'; // add, foobar, x, y, ...

    const INT = 'INT'; // 1343456

    // Operators
    const ASSIGN = '=';

    const PLUS = '+';

    const MINUS = '-';

    const BANG = '!';

    const ASTERISK = '*';

    const SLASH = '/';

    const LT = '<';

    const GT = '>';

    const EQ = '==';

    const NOT_EQ = '!=';

    // Delimiters
    const COMMA = ',';

    const SEMICOLON = ';';

    const LPAREN = '(';

    const RPAREN = ')';

    const LBRACE = '{';

    const RBRACE = '}';

    // Keywords
    const FUNCTION = 'FUNCTION';

    const LET = 'LET';

    const TRUE = 'TRUE';

    const FALSE = 'FALSE';

    const IF = 'IF';

    const ELSE = 'ELSE';

    const RETURN = 'RETURN';
}
