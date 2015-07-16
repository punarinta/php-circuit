<?php

require_once 'Circuit.php';
require_once 'Analysis.php';
require_once 'Format.php';

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
                    $e = new Element($w[0], Element::RESISTOR, Format::toFloat($w[3]));
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'C';
                    $e = new Element($w[0], Element::CAPACITOR, Format::toFloat($w[3]));
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'L';
                    $e = new Element($w[0], Element::INDUCTOR, Format::toFloat($w[3]));
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'I';
                    $e = new Element($w[0], Element::CURRENT, Format::toFloat($w[4]));
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;

                case 'V';
                    $e = new Element($w[0], Element::VOLTAGE, Format::toFloat($w[4]));
                    $e->pins = $circuit->pushNodes([$w[1], $w[2]]);
                    $circuit->elements[] = $e;
                    break;
            }

            switch ($w[0])
            {
                case '.DC':
                    // .DC var start stop step
                    if (count($w) < 5) break;

                    $ops = Analysis::dc($circuit, $w[1], Format::toFloat($w[2]), Format::toFloat($w[3]), Format::toFloat($w[4]));
                    break;

                case '.AC':
                    // .AC type np start stop
                    if (count($w) < 5) break;

                    $ops = Analysis::ac($circuit, $w[1], strtoupper($w[2]), Format::toFloat($w[3]), Format::toFloat($w[4]));
                    break;

                case '.TRAN':
                    if (count($w) < 3) break;

                    // support only step/stop for now
                    $ops = Analysis::tran($circuit, Format::toFloat($w[1]), Format::toFloat($w[2]));
                    break;

                case '.PLOT':
                    // very basic support for now

                    preg_match('/\(([A-Za-z0-9 ]+?)\)/', $w[2], $m);
                    $nodeName = $m[1];
                    $nodeId = $circuit->findNodeByName($nodeName);

                    $w[1] = strtoupper($w[1]);

                    foreach ($ops as $x => $row)
                    {
                        if ($w[1] == 'DC') echo "V($nodeName) [VDC=" . Format::toString($x), "] = " . Format::toString($row[$nodeId - 1]) . "V\n";
                        if ($w[1] == 'AC') echo "V($nodeName) [f=" . Format::toString($x), "] = " . Format::toString($row[$nodeId - 1]) . "V\n";
                        if ($w[1] == 'TRAN') echo "V($nodeName) [t=" . Format::toString($x), "] = " . Format::toString($row[$nodeId - 1]) . "V\n";
                    }

                    break;
            }
        }

        return $circuit;
    }
}
