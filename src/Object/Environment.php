<?php

namespace Summer\West\Object;

class Environment
{
    private array $store = [];

    private ?Environment $outer;

    /**
     * Constructor for the Environment.
     *
     * @param  Environment|null  $outer  The enclosing environment, if any.
     */
    public function __construct(?Environment $outer = null)
    {
        $this->outer = $outer;
    }

    /**
     * Creates a new enclosed environment with the current environment as the outer.
     */
    public static function newEnclosedEnvironment(Environment $outer): Environment
    {
        return new self($outer);
    }

    /**
     * Gets a value from the environment by its name.
     *
     * @param  string  $name  The name of the variable.
     * @return array [Object|null, bool] Returns the value and whether it was found.
     */
    public function get(string $name): array
    {
        if (array_key_exists($name, $this->store)) {
            return [$this->store[$name], true];
        }

        if ($this->outer !== null) {
            return $this->outer->get($name);
        }

        return [null, false];
    }

    /**
     * Sets a value in the environment.
     *
     * @param  string  $name  The name of the variable.
     * @param  WestObject  $val  The value to set.
     * @return WestObject The value that was set.
     */
    public function set(string $name, object $val): object
    {
        $this->store[$name] = $val;

        return $val;
    }
}
