<?php

namespace Summer\West\Parser;

enum PrecedenceLevel: int
{
    case LOWEST = 1;
    case EQUALS = 2;
    case LESSGREATER = 3;
    case SUM = 4;
    case PRODUCT = 5;
    case PREFIX = 6;
    case CALL = 7;
}
