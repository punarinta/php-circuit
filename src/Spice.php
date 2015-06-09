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
                    // .dc vs 0 10 1
                    if (count($w) < 5)
                    {
                        // broken input
                        break;
                    }

                    $ops = Analysis::dc($circuit, $w[1], $w[2], $w[3], $w[4]);

                    break;

                case '.PLOT':
                    // very basic support for now

                    preg_match('/\(([A-Za-z0-9 ]+?)\)/', $w[2], $m);
                    $nodeName = $m[1];
                    $nodeId = $circuit->findNodeByName($nodeName);

                    foreach ($ops as $V => $row)
                    {
                        echo "V($nodeName) [VDC=$V] \t\t = " . number_format($row[$nodeId - 1], 6) . " V\n";
                    }

                    break;
            }
        }

        return $circuit;
    }
}
