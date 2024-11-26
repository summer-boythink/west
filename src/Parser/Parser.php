<?php

namespace Summer\West\Parser;

use Summer\West\Ast\Expression;
use Summer\West\Ast\Program;
use Summer\West\Ast\Statement;
use Summer\West\Lexer\Lexer;
use Summer\West\Parser\Expression\IdentifierParser;
use Summer\West\Parser\Expression\IExpression;
use Summer\West\Parser\Expression\IinfixExpression;
use Summer\West\Parser\Expression\InfixExpressionParser;
use Summer\West\Parser\Expression\IntegerLiteralParser;
use Summer\West\Parser\Expression\PrefixExpressionParser;
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
        $this->registerPrefix(TokenType::IDENT, IdentifierParser::class);
        $this->registerPrefix(TokenType::INT, IntegerLiteralParser::class);
        $this->registerPrefix(TokenType::BANG, PrefixExpressionParser::class);
        $this->registerPrefix(TokenType::MINUS, PrefixExpressionParser::class);

        // 注册中缀解析函数
        $this->registerInfix(TokenType::PLUS, InfixExpressionParser::class);
        $this->registerInfix(TokenType::MINUS, InfixExpressionParser::class);
        $this->registerInfix(TokenType::ASTERISK, InfixExpressionParser::class);
        $this->registerInfix(TokenType::SLASH, InfixExpressionParser::class);
        $this->registerInfix(TokenType::EQ, InfixExpressionParser::class);
        $this->registerInfix(TokenType::NOT_EQ, InfixExpressionParser::class);
        $this->registerInfix(TokenType::LT, InfixExpressionParser::class);
        $this->registerInfix(TokenType::GT, InfixExpressionParser::class);

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
        return match ($this->curToken->type) {
            TokenType::LET => $this->letParser->parse(),
            TokenType::RETURN => $this->returnStatementParser->parse(),
            default => $this->expressionStatementParser->parse(),
        };
    }

    public function registerPrefix(TokenType $type, string $parserClass): void
    {
        $this->prefixParseFns[$type->name] = function () use ($parserClass) {

            /** @var IExpression $parser */
            $parser = new $parserClass($this);

            return $parser->parse();
        };
    }

    public function registerInfix(TokenType $type, string $parserClass): void
    {
        $this->infixParseFns[$type->name] = function (Expression $left) use ($parserClass) {
            /** @var IinfixExpression $parser */
            $parser = new $parserClass($this);

            return $parser->parse($left);
        };
    }

    public function parseExpression(PrecedenceLevel $precedence): ?Expression
    {
        $prefix = $this->prefixParseFns[$this->curToken->type->name] ?? null;
        if ($prefix === null) {
            $this->addError("No prefix parse function for {$this->curToken->type->name}");

            return null;
        }

        /** @var Expression $leftExp */
        $leftExp = $prefix();

        while (! $this->peekTokenIs(TokenType::SEMICOLON) && $precedence->value < $this->getPeekPrecedence()->value) {
            $infix = $this->infixParseFns[$this->peekToken->type->name] ?? null;
            if ($infix === null) {
                return $leftExp;
            }

            $this->nextToken();
            $leftExp = $infix($leftExp);
        }

        return $leftExp;
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

    public function getCurrentPrecedence(): PrecedenceLevel
    {
        return Precedence::getPrecedence($this->curToken->type);
    }

    public function getPeekPrecedence(): PrecedenceLevel
    {
        return Precedence::getPrecedence($this->peekToken->type);
    }

    public function next(): void
    {
        $this->nextToken();
    }
}
