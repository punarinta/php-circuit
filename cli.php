<?php

require_once 'src/Circuit.php';

if ($argc < 2)
{
    die("No input file specified.\n\n");
}

$c = new Circuit;
$c->fromSpice(file_get_contents($argv[1]));
$c->fillCurrents();
$c->fillTransconductances();
$c->solve();

/*print_r($c->elements);
print_r($c->nodes);
echo Matrix::show($c->G);
echo 'I: ' . implode(' ', $c->I) . "\n\n";*/

echo "Solution:\n\n";
foreach ($c->V as $k => $v)
{
    echo "V(" . $c->getNodeName($k) . ") = " . number_format($v, 6) . " V\n";
}

echo "\n";
