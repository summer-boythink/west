<?php

namespace Tests;

use Summer\West\Evaluator\Evaluator;
use Summer\West\Lexer\Lexer;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestObject;
use Summer\West\Parser\Parser;

it('evaluates integer expressions correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with([
    ['5', 5],
    ['10', 10],
]);

/**
 * Evaluates the input and returns the resulting WestObject.
 *
 * @param  string  $input  The input to evaluate.
 * @return WestObject|null The evaluated object.
 */
function testEval(string $input): ?WestObject
{
    $lexer = new Lexer($input);
    $parser = new Parser($lexer);
    $program = $parser->parseProgram();

    // Using the Evaluator to evaluate the program
    return Evaluator::eval($program);
}

/**
 * Tests if the evaluated object is an Integer and matches the expected value.
 *
 * @param  WestObject  $obj  The evaluated object.
 * @param  int  $expected  The expected integer value.
 */
function testIntegerObject(WestObject $obj, int $expected)
{
    /** @var WestInteger $obj */
    expect($obj)->toBeInstanceOf(WestInteger::class);  // Expecting Integer object
    expect($obj->value)->toBe($expected);  // Expect the value to match the expected
}
