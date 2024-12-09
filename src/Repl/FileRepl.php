<?php

namespace Summer\West\Repl;

use Summer\West\Evaluator\Evaluator;
use Summer\West\Lexer\Lexer;
use Summer\West\Object\Environment;
use Summer\West\Parser\Parser;

class FileRepl
{
    public function start($in, $out = STDOUT, $err_out = STDERR)
    {
        if (! file_exists($in)) {
            fwrite($err_out, "Error: File not found - $in\n");
            exit(1);
        }

        $input = file_get_contents($in);
        $lexer = new Lexer($input);
        $parser = new Parser($lexer);
        $program = $parser->parseProgram();

        if (count($parser->getErrors()) > 0) {
            fwrite($err_out, "Parser errors:\n");
            foreach ($parser->getErrors() as $error) {
                fwrite($err_out, "\t$error\n");
            }
            exit(1);
        }

        $env = new Environment;
        $evaluated = Evaluator::eval($program, $env);

        if ($evaluated !== null) {
            fwrite($out, $evaluated->inspect()."\n");
        }
    }
}
