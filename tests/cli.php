<?php

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 2)
{
    die("No input file specified.\n\n");
}

$c = \PhpCircuit\Spice::import(file_get_contents($argv[1]));

echo "\n";
