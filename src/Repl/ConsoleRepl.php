<?php

namespace Summer\West\Repl;

use Summer\West\Evaluator\Evaluator;
use Summer\West\Lexer\Lexer;
use Summer\West\Object\Environment;
use Summer\West\Parser\Parser;

class ConsoleRepl
{
    const PROMPT = '>> ';

    public function start($in, $out)
    {
        $env = new Environment;
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
                // If no errors, evaluate the program and print the result
                $evaluated = Evaluator::eval($program, $env);

                // Print the result if it is not null
                if ($evaluated !== null) {
                    fwrite($out, $evaluated->inspect()."\n");
                }
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
