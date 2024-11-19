<?php

namespace Summer\West\Parser;

class Precedence
{
    public const LOWEST = 1;

    public const EQUALS = 2;      // ==

    public const LESSGREATER = 3; // > or <

    public const SUM = 4;         // +

    public const PRODUCT = 5;     // *

    public const PREFIX = 6;      // -X or !X

    public const CALL = 7;        // myFunction(X)
}
