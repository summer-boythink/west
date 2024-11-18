<?php

namespace Summer\West\Parser;

use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
use Summer\West\Ast\Program;
use Summer\West\Ast\Statement;
use Summer\West\Lexer\Lexer;
use Summer\West\Token\Token;
use Summer\West\Token\TokenType;

class Parser
{
    private Lexer $lexer;

    private ?Token $curToken = null;

    private ?Token $peekToken = null;

    private array $errors = []; // 添加错误存储

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;

        // 初始化 curToken 和 peekToken
        $this->nextToken();
        $this->nextToken();
    }

    public function getErrors(): array
    {
        return $this->errors; // 返回错误列表
    }

    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    private function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
    }

    public function parseProgram(): Program
    {
        $program = new Program;
        $program->statements = [];

        while ($this->curToken !== null && $this->curToken->type !== TokenType::EOF) {
            $stmt = $this->parseStatement();

            if ($stmt !== null) {
                $program->statements[] = $stmt;
            }

            $this->nextToken();
        }

        return $program;
    }

    private function parseStatement(): ?Statement
    {
        switch ($this->curToken->type) {
            case TokenType::LET:
                return $this->parseLetStatement();
            default:
                $this->addError("Unrecognized statement at token: {$this->curToken->literal}");

                return null;
        }
    }

    private function parseLetStatement(): ?LetStatement
    {
        $letToken = $this->curToken;

        if (! $this->expectPeek(TokenType::IDENT)) {
            $this->addError("Expected IDENT after LET, but got {$this->peekToken?->type}");

            return null;
        }

        $name = new Identifier($this->curToken, $this->curToken->literal);
        $stmt = new LetStatement($letToken, $name, null);

        if (! $this->expectPeek(TokenType::ASSIGN)) {
            $this->addError("Expected ASSIGN after IDENT, but got {$this->peekToken?->type}");

            return null;
        }

        while ($this->curToken->type !== TokenType::SEMICOLON && $this->curToken->type !== TokenType::EOF) {
            $this->nextToken();
        }

        return $stmt;
    }

    private function curTokenIs(TokenType $type): bool
    {
        return $this->curToken->type === $type;
    }

    private function peekTokenIs(TokenType $type): bool
    {
        return $this->peekToken?->type === $type;
    }

    private function expectPeek(TokenType $type): bool
    {
        if ($this->peekTokenIs($type)) {
            $this->nextToken();

            return true;
        }

        $this->addError("Expected next token to be {$type->name}, got {$this->peekToken?->type} instead");

        return false;
    }
}
