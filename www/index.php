<?php
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));

// Setup autoloading
require 'vendor/autoload.php';

use zaboy\res\NameSpase;

$aa = new NameSpase\NameOfClass();
echo($aa->sumAB(1, 2));