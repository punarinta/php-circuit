<?php

require_once 'src/Spice.php';
require_once 'src/Analysis.php';

if ($argc < 2)
{
    die("No input file specified.\n\n");
}

$c = Spice::import(file_get_contents($argv[1]));

echo "\n";
