<?php

namespace Summer\West\Parser;

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

    private array $errors = [];

    private LetStatementParser $letParser;

    private ReturnStatementParser $returnStatementParser;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;

        // 初始化 curToken 和 peekToken
        $this->nextToken();
        $this->nextToken();

        // 初始化 let 语句解析器
        $this->letParser = new LetStatementParser($this);
        $this->returnStatementParser = new ReturnStatementParser($this);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $message): void
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
                return $this->letParser->parse(); // 调用 LetStatementParser 的解析方法
            case TokenType::RETURN:
                return $this->returnStatementParser->parse();
            default:
                $this->addError("Unrecognized statement at token: {$this->curToken->literal}");

                return null;
        }
    }

    public function curTokenIs(TokenType $type): bool
    {
        return $this->curToken->type === $type;
    }

    public function peekTokenIs(TokenType $type): bool
    {
        return $this->peekToken?->type === $type;
    }

    public function expectPeek(TokenType $type): bool
    {
        if ($this->peekTokenIs($type)) {
            $this->nextToken();

            return true;
        }

        $this->addError("Expected next token to be {$type->name}, got {$this->peekToken?->type->name} instead");

        return false;
    }

    public function getCurToken(): ?Token
    {
        return $this->curToken;
    }

    public function getPeekToken(): ?Token
    {
        return $this->peekToken;
    }

    public function next(): void
    {
        $this->nextToken();
    }
}
