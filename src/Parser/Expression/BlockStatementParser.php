<?php

namespace Summer\West\Parser\Expression;

use Summer\West\Ast\BlockStatement;
use Summer\West\Parser\Parser;
use Summer\West\Token\TokenType;

class BlockStatementParser
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(): BlockStatement
    {
        $token = $this->parser->getCurToken();

        $statements = [];

        $this->parser->next(); // 移动到块中的第一个 Token

        while (! $this->parser->curTokenIs(TokenType::RBRACE) && ! $this->parser->curTokenIs(TokenType::EOF)) {
            $stmt = $this->parser->parseStatement();
            if ($stmt !== null) {
                $statements[] = $stmt;
            }
            $this->parser->next();
        }

        return new BlockStatement($token, $statements);
    }
}
