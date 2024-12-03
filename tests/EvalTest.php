<?php

namespace Tests;

use Summer\West\Evaluator\Evaluator;
use Summer\West\Lexer\Lexer;
use Summer\West\Object\WestBoolean;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestObject;
use Summer\West\Parser\Parser;

it('evaluates integer expressions correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with([
    ['5', 5],
    ['10', 10],
    ['-5', -5],
    ['-10', -10],
]);

it('evaluates boolean expressions correctly', function (string $input, bool $expected) {
    $evaluated = testEval($input);
    testBooleanObject($evaluated, $expected);
})->with([
    ['true', true],
    ['false', false],
]);

it('evaluates bang operator correctly', function (string $input, bool $expected) {
    $evaluated = testEval($input);
    testBooleanObject($evaluated, $expected);
})->with([
    ['!true', false],
    ['!false', true],
    ['!5', false],
    ['!!true', true],
    ['!!false', false],
    ['!!5', true],
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

/**
 * Tests if the evaluated object is a Boolean and matches the expected value.
 *
 * @param  WestObject  $obj  The evaluated object.
 * @param  bool  $expected  The expected boolean value.
 */
function testBooleanObject($obj, bool $expected)
{
    /** @var WestBoolean $obj */
    expect($obj)->toBeInstanceOf(WestBoolean::class);
    expect($obj->value)->toBe($expected);
}
