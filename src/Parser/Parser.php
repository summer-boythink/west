<?php

namespace Summer\West\Parser;

use Summer\West\Ast\Identifier;
use Summer\West\Ast\LetStatement;
use Summer\West\AST\Program;
use Summer\West\AST\Statement;
use Summer\West\Lexer\Lexer;
use Summer\West\Token\Token;
use Summer\West\Token\TokenType;

class Parser
{
    private Lexer $lexer;

    private ?Token $curToken = null;

    private ?Token $peekToken = null;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;

        // 初始化 curToken 和 peekToken
        $this->nextToken();
        $this->nextToken();
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
            } else {

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
                // 记录未识别的语句类型
                echo "Unrecognized statement at token: {$this->curToken->literal}\n";

                return null;
        }
    }

    private function parseLetStatement(): ?LetStatement
    {
        // 当前 token 应为 "let"
        $letToken = $this->curToken;

        // 期望下一个 token 是标识符 (IDENT)
        if (! $this->expectPeek(TokenType::IDENT)) {
            return null;
        }

        // 创建 LetStatement，并设置 Name 为标识符
        $name = new Identifier($this->curToken, $this->curToken->literal);
        $stmt = new LetStatement($letToken, $name, null);

        // 期望下一个 token 是赋值符号 (=)
        if (! $this->expectPeek(TokenType::ASSIGN)) {
            return null;
        }

        // 跳过对赋值表达式的处理，直到遇到分号
        while ($this->curToken->type !== TokenType::SEMICOLON) {
            $this->nextToken();
        }

        return $stmt;
    }

    private function curTokenIs(TokenType $type): bool
    {
        return $this->curToken->type == $type;
    }

    private function peekTokenIs(TokenType $type): bool
    {
        return $this->peekToken->type == $type;
    }

    private function expectPeek(TokenType $type): bool
    {
        if ($this->peekToken !== null && $this->peekToken->type === $type) {
            $this->nextToken();

            return true;
        }

        return false;
    }
}
