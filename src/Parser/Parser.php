<?php

namespace Summer\West\Parser;

use Summer\West\Ast\Expression;
use Summer\West\Ast\Identifier;
use Summer\West\Ast\Program;
use Summer\West\Ast\Statement;
use Summer\West\Lexer\Lexer;
use Summer\West\Parser\Statement\ExpressionStatementParser;
use Summer\West\Parser\Statement\LetStatementParser;
use Summer\West\Parser\Statement\ReturnStatementParser;
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

    private ExpressionStatementParser $expressionStatementParser;

    /** @var array<TokenType, callable|null> */
    private array $prefixParseFns = [];

    /** @var array<TokenType, callable|null> */
    private array $infixParseFns = [];

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;

        // 初始化 curToken 和 peekToken
        $this->nextToken();
        $this->nextToken();

        // 初始化 let 语句解析器
        $this->letParser = new LetStatementParser($this);
        $this->returnStatementParser = new ReturnStatementParser($this);
        $this->expressionStatementParser = new ExpressionStatementParser($this);

        // 注册前缀解析函数
        $this->registerPrefix(TokenType::IDENT, fn () => $this->parseIdentifier());
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
                return $this->expressionStatementParser->parse();
        }
    }

    public function registerPrefix(TokenType $type, callable $fn): void
    {
        $this->prefixParseFns[$type->name] = $fn;
    }

    public function registerInfix(TokenType $type, callable $fn): void
    {
        $this->infixParseFns[$type->name] = $fn;
    }

    public function parseExpression(int $precedence): ?Expression
    {
        $prefix = $this->prefixParseFns[$this->curToken->type->name] ?? null;
        if ($prefix === null) {
            $this->addError("No prefix parse function for {$this->curToken->type->name}");

            return null;
        }

        return $prefix();
    }

    private function parseIdentifier(): Identifier
    {
        return new Identifier($this->curToken, $this->curToken->literal);
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
