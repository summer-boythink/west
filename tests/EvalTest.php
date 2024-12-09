<?php

namespace Tests;

use Summer\West\Evaluator\Evaluator;
use Summer\West\Lexer\Lexer;
use Summer\West\Object\Environment;
use Summer\West\Object\WestBoolean;
use Summer\West\Object\WestError;
use Summer\West\Object\WestFunction;
use Summer\West\Object\WestInteger;
use Summer\West\Object\WestObject;
use Summer\West\Object\WestString;
use Summer\West\Parser\Parser;

it('evaluates integer expressions correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with([
    ['5', 5],
    ['10', 10],
    ['-5', -5],
    ['-10', -10],
    ['5 + 5 + 5 + 5 - 10', 10],
    ['2 * 2 * 2 * 2 * 2', 32],
    ['-50 + 100 + -50', 0],
    ['5 * 2 + 10', 20],
    ['5 + 2 * 10', 25],
    ['20 + 2 * -10', 0],
    ['50 / 2 * 2 + 10', 60],
    ['2 * (5 + 10)', 30],
    ['3 * 3 * 3 + 10', 37],
    ['3 * (3 * 3) + 10', 37],
    ['(5 + 10 * 2 + 15 / 3) * 2 + -10', 50],
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
    ['true == true', true],
    ['false == false', true],
    ['true == false', false],
    ['true != false', true],
    ['false != true', true],
    ['(1 < 2) == true', true],
    ['(1 < 2) == false', false],
    ['(1 > 2) == true', false],
    ['(1 > 2) == false', true],
]);

it('evaluates if-else expressions correctly', function (string $input, mixed $expected) {
    $evaluated = testEval($input);

    if (is_int($expected)) {
        testIntegerObject($evaluated, $expected);
    } else {
        testNullObject($evaluated);
    }
})->with([
    ['if (true) { 10 }', 10],
    ['if (false) { 10 }', null],
    ['if (1) { 10 }', 10],
    ['if (1 < 2) { 10 }', 10],
    ['if (1 > 2) { 10 }', null],
    ['if (1 > 2) { 10 } else { 20 }', 20],
    ['if (1 < 2) { 10 } else { 20 }', 10],
]);

it('evaluates return statements correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with([
    ['return 10;', 10],
    ['return 10; 9;', 10],
    ['return 2 * 5; 9;', 10],
    ['9; return 2 * 5; 9;', 10],
    [
        '
if (10 > 1) {
  if (10 > 1) {
    return 10;
  }

  return 1;
}
',
        10,
    ],
]);

it('handles errors correctly', function (string $input, string $expectedMessage) {
    $evaluated = testEval($input);

    // Ensure the result is an instance of WestError
    expect($evaluated)->toBeInstanceOf(WestError::class);

    /** @var WestError $evaluated */
    expect($evaluated->message)->toBe($expectedMessage);
})->with([
    ['5 + true;', 'type mismatch: INTEGER + BOOLEAN'],
    ['5 + true; 5;', 'type mismatch: INTEGER + BOOLEAN'],
    ['-true', 'unknown operator: -BOOLEAN'],
    ['true + false;', 'unknown operator: BOOLEAN + BOOLEAN'],
    ['5; true + false; 5', 'unknown operator: BOOLEAN + BOOLEAN'],
    ['if (10 > 1) { true + false; }', 'unknown operator: BOOLEAN + BOOLEAN'],
    [
        '
if (10 > 1) {
  if (10 > 1) {
    return true + false;
  }

  return 1;
}
',
        'unknown operator: BOOLEAN + BOOLEAN',
    ],
]);

it('evaluates let statements correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with(
    [
        ['let a = 5; a;', 5],
        ['let a = 5 * 5; a;', 25],
        ['let a = 5; let b = a; b;', 5],
        ['let a = 5; let b = a; let c = a + b + 5; c;', 15],
    ]
);

it('evaluates function objects correctly', function () {
    $input = 'fn(x) { x + 2; }';

    $evaluated = testEval($input);
    testFunctionObject($evaluated, ['x'], '(x + 2)');
});

it('evaluates function applications correctly', function (string $input, int $expected) {
    $evaluated = testEval($input);
    testIntegerObject($evaluated, $expected);
})->with([
    ['let identity = fn(x) { x; }; identity(5);', 5],
    ['let identity = fn(x) { return x; }; identity(5);', 5],
    ['let double = fn(x) { x * 2; }; double(5);', 10],
    ['let add = fn(x, y) { x + y; }; add(5, 5);', 10],
    ['let add = fn(x, y) { x + y; }; add(5 + 5, add(5, 5));', 20],
    ['fn(x) { x; }(5)', 5],
]);

it('evaluates closures correctly', function () {
    $input = '
let newAdder = fn(x) {
  fn(y) { x + y };
};

let addTwo = newAdder(2);
addTwo(2);
';

    $evaluated = testEval($input);
    testIntegerObject($evaluated, 4);
});

it('parses string literals correctly', function (string $input, string $expected) {

    $evaluated = testEval($input);

    // Ensure the result is a WestString object
    expect($evaluated)->toBeInstanceOf(WestString::class);

    /** @var WestString $evaluated */
    expect($evaluated->value)->toBe($expected);
})->with([
    ['"hello world";', 'hello world'],
    ['"foobar";', 'foobar'],
    ['"foo bar";', 'foo bar'],
    ['"Hello" + " " + "World!"', 'Hello World!'],
]);

it('handles errors correctly with bad adds', function (string $input, string $expectedMessage) {
    $evaluated = testEval($input);

    // 确保评估结果是 WestError 对象
    expect($evaluated)->toBeInstanceOf(WestError::class);

    /** @var WestError $evaluated */
    expect($evaluated->message)->toBe($expectedMessage);
})->with([
    ['5 + true;', 'type mismatch: INTEGER + BOOLEAN'],
    ['5 + true; 5;', 'type mismatch: INTEGER + BOOLEAN'],
    ['-true', 'unknown operator: -BOOLEAN'],
    ['true + false;', 'unknown operator: BOOLEAN + BOOLEAN'],
    ['5; true + false; 5', 'unknown operator: BOOLEAN + BOOLEAN'],
    ['if (10 > 1) { true + false; }', 'unknown operator: BOOLEAN + BOOLEAN'],
    ['"Hello" - "World"', 'unknown operator: STRING - STRING'],
]);

/**
 * Tests if the evaluated object is a WestFunction and matches the expected parameters and body.
 *
 * @param  string[]  $expectedParams
 */
function testFunctionObject(WestObject $obj, array $expectedParams, string $expectedBody)
{
    expect($obj)->toBeInstanceOf(WestFunction::class);

    // Check parameters count
    /** @var WestFunction $obj */
    expect(count($obj->parameters))->toBe(count($expectedParams));

    // Check each parameter name
    foreach ($expectedParams as $index => $expectedParam) {
        expect($obj->parameters[$index]->value)->toBe($expectedParam);
    }

    // Check body
    expect((string) $obj->body)->toBe($expectedBody);
}

/**
 * Tests if the evaluated object is NULL.
 *
 * @param  WestObject|null  $obj  The evaluated object.
 */
function testNullObject(?WestObject $obj)
{
    expect($obj)->toBeNull();  // Expect the object to be null
}

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
    $env = new Environment;

    // Using the Evaluator to evaluate the program
    return Evaluator::eval($program, $env);
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
