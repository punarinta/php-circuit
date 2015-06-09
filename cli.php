<?php

require_once 'src/Analysis.php';

if ($argc < 2)
{
    die("No input file specified.\n\n");
}

$c = new Circuit;
$c->fromSpice(file_get_contents($argv[1]));

Analysis::dcop($c);
Analysis::showVoltages($c);
