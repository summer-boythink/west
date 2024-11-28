<?php

namespace Summer\West\Repl;

use Summer\West\Lexer\Lexer;
use Summer\West\Parser\Parser;

class Repl
{
    const PROMPT = '>> ';

    public function start($in, $out)
    {
        // Initialize the input scanner (similar to Go's bufio.Scanner)
        while (true) {
            // Print the prompt
            fwrite($out, self::PROMPT);

            // Read the input line
            $line = fgets($in);

            // Exit if end of input (EOF) is encountered
            if ($line === false) {
                break;
            }

            // Trim any extra whitespace from the input
            $line = rtrim($line);

            // Tokenize the input using the lexer
            $lexer = new Lexer($line);
            $parser = new Parser($lexer);

            // Parse the program and check for errors
            $program = $parser->parseProgram();

            if (count($parser->getErrors()) > 0) {
                $this->printParserErrors($out, $parser->getErrors());
            } else {
                // If no errors, print the program's string representation
                fwrite($out, $program->__toString()."\n");
            }
        }
    }

    private function printParserErrors($out, array $errors)
    {
        foreach ($errors as $error) {
            fwrite($out, "\t$error\n");
        }
    }
}
