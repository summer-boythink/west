<?php

require 'vendor/autoload.php';

use Summer\West\Repl\Repl;

// Initialize the REPL
$repl = new Repl();
$repl->start(STDIN, STDOUT);
