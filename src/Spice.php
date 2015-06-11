<?php

require_once 'Circuit.php';
require_once 'Analysis.php';

class Spice
{
    /**
     * Imports schematics from SPICE data
     *
     * @param $code
     * @return Circuit
     */
    static public function import($code)
    {
        $ops = [];
        $circuit = new Circuit;

        foreach (explode("\n", $code) as $line)
        {
            $w = explode(' ', trim($line));

            if (!isset ($w[0][0]))
            {
                continue;
            }

            if ($w[0][0] == '*')
            {
                // that's a comment
                continue;
            }

            $w[0] = strtoupper($w[0]);

            switch ($w[0][0])
            {
                case 'R';
                    $e = new Element($w[0], Element::TYPE_RESISTOR, $w[3]);
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'C';
                    $e = new Element($w[0], Element::TYPE_CAPACITOR, $w[3]);
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'L';
                    $e = new Element($w[0], Element::TYPE_INDUCTOR, $w[3]);
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'I';
                    $e = new Element($w[0], Element::TYPE_CURRENT, $w[4]);
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'V';
                    $e = new Element($w[0], Element::TYPE_VOLTAGE, $w[4]);
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;
            }

            switch ($w[0])
            {
                case '.DC':
                    // .DC var start stop step
                    if (count($w) < 5) break;

                    $ops = Analysis::dc($circuit, $w[1], $w[2], $w[3], $w[4]);
                    break;

                case '.AC':
                    // .AC type np start stop
                    if (count($w) < 5) break;

                    $ops = Analysis::ac($circuit, $w[1], $w[2], $w[3], $w[4]);
                    break;

                case '.PLOT':
                    // very basic support for now

                    preg_match('/\(([A-Za-z0-9 ]+?)\)/', $w[2], $m);
                    $nodeName = $m[1];
                    $nodeId = $circuit->findNodeByName($nodeName);

                    $w[1] = strtoupper($w[1]);

                    foreach ($ops as $x => $row)
                    {
                        if ($w[1] == 'DC') echo "V($nodeName) [VDC=" . sprintf('%.3e', $x), "] \t\t = " . sprintf('%.3e', ($row[$nodeId - 1])) . " V\n";
                        if ($w[1] == 'AC') echo "V($nodeName) [f=" . sprintf('%.3e', $x), "] \t\t = " . sprintf('%.3e', ($row[$nodeId - 1])) . " V\n";
                    }

                    break;
            }
        }

        return $circuit;
    }
}
