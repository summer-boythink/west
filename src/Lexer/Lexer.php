<?php

namespace Summer\West\Lexer;

use Summer\West\Token\Token;
use Summer\West\Token\Tokenizer;
use Summer\West\Token\TokenType;

class Lexer
{
    private string $input;

    private int $position = 0;       // Current position in input (points to current char)

    private int $readPosition = 0;    // Current reading position in input (after current char)

    private string $ch = '';         // Current char under examination

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->readChar();
    }

    public function nextToken(): Token
    {
        $this->skipWhitespace();

        // Define a token placeholder
        $tok = new Token(TokenType::ILLEGAL, '');

        switch ($this->ch) {
            case '=':
                if ($this->peekChar() === '=') {
                    $ch = $this->ch;
                    $this->readChar();
                    $literal = "{$ch}{$this->ch}";
                    $tok = new Token(TokenType::EQ, $literal);
                } else {
                    $tok = $this->newToken(TokenType::ASSIGN, $this->ch);
                }
                break;

            case '+':
                $tok = $this->newToken(TokenType::PLUS, $this->ch);
                break;

            case '-':
                $tok = $this->newToken(TokenType::MINUS, $this->ch);
                break;

            case '!':
                if ($this->peekChar() === '=') {
                    $ch = $this->ch;
                    $this->readChar();
                    $literal = $ch.$this->ch;
                    $tok = new Token(TokenType::NOT_EQ, $literal);
                } else {
                    $tok = $this->newToken(TokenType::BANG, $this->ch);
                }
                break;

            case '/':
                $tok = $this->newToken(TokenType::SLASH, $this->ch);
                break;

            case '*':
                $tok = $this->newToken(TokenType::ASTERISK, $this->ch);
                break;

            case '<':
                $tok = $this->newToken(TokenType::LT, $this->ch);
                break;

            case '>':
                $tok = $this->newToken(TokenType::GT, $this->ch);
                break;

            case ';':
                $tok = $this->newToken(TokenType::SEMICOLON, $this->ch);
                break;

            case ',':
                $tok = $this->newToken(TokenType::COMMA, $this->ch);
                break;

            case '{':
                $tok = $this->newToken(TokenType::LBRACE, $this->ch);
                break;

            case '}':
                $tok = $this->newToken(TokenType::RBRACE, $this->ch);
                break;

            case '(':
                $tok = $this->newToken(TokenType::LPAREN, $this->ch);
                break;

            case ')':
                $tok = $this->newToken(TokenType::RPAREN, $this->ch);
                break;

            case '':
                // End of input reached, return EOF token
                return new Token(TokenType::EOF, '');

            default:
                if ($this->isLetter($this->ch)) {
                    $literal = $this->readIdentifier();
                    // Check if the identifier is a keyword
                    $tok = new Token(Tokenizer::lookupIdent($literal), $literal);

                    return $tok;
                } elseif ($this->isDigit($this->ch)) {
                    $literal = $this->readNumber();
                    $tok = new Token(TokenType::INT, $literal);

                    return $tok;
                } else {
                    // If it's an unrecognized character, return an ILLEGAL token
                    $tok = $this->newToken(TokenType::ILLEGAL, $this->ch);
                }
        }

        $this->readChar(); // Move to the next character

        return $tok;
    }

    private function skipWhitespace(): void
    {
        // Use correct unescaped characters for whitespace
        while (in_array($this->ch, [' ', "\t", "\n", "\r"])) {
            $this->readChar();
        }
    }

    private function readChar(): void
    {
        if ($this->readPosition >= strlen($this->input)) {
            $this->ch = '';
        } else {
            $this->ch = $this->input[$this->readPosition];
        }

        $this->position = $this->readPosition;
        $this->readPosition++;
    }

    private function peekChar(): string
    {
        if ($this->readPosition >= strlen($this->input)) {
            return '';
        } else {
            return $this->input[$this->readPosition];
        }
    }

    private function readIdentifier(): string
    {
        $position = $this->position;
        while ($this->isLetter($this->ch)) {
            $this->readChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    private function readNumber(): string
    {
        $position = $this->position;
        while ($this->isDigit($this->ch)) {
            $this->readChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    private function isLetter(string $ch): bool
    {
        return preg_match('/[a-zA-Z_]/', $ch) === 1;
    }

    private function isDigit(string $ch): bool
    {
        return preg_match('/\d/', $ch) === 1;
    }

    private function newToken(TokenType $tokenType, string $ch): Token
    {
        return new Token($tokenType, $ch);
    }
}
