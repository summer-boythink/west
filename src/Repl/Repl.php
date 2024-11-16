<?php

namespace Summer\West\Repl;

use Summer\West\Lexer\Lexer;
use Summer\West\Token\TokenType;

class Repl
{
    const PROMPT = '>> ';

    public function start($in, $out)
    {
        // Read input from stdin (or any stream) and write output to stdout (or any stream)
        while (true) {
            // Print the prompt
            fwrite($out, self::PROMPT);

            // Read a line of input
            $line = fgets($in);

            // If we reach the end of input (EOF), break out of the loop
            if ($line === false) {
                break;
            }

            // Create a new lexer instance and tokenize the input line
            $lexer = new Lexer($line);

            // Loop through the tokens produced by the lexer
            while (($tok = $lexer->nextToken())->getType() !== TokenType::EOF) {
                // Print the token in a more readable format
                fwrite($out, $this->formatToken($tok)."\n");
            }
        }
    }

    private function formatToken($token)
    {
        // Here, you can adjust what properties of the token you want to display
        return sprintf(
            'Type: %-9s Literal: %s',
            $token->getType(),
            $token->getLiteral()
        );
    }
}
