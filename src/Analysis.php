<?php

require_once 'Circuit.php';

class Analysis
{
    /**
     * Calculates DC operating point
     *
     * @param $circuit
     * @throws Exception
     */
    static public function dcop($circuit)
    {
        if (!count($circuit->elements))
        {
            throw new \Exception('Circuit does not contain elements');
        }

        $circuit->prepare();
        $circuit->solve();
    }

    /**
     * Shows voltages in the nodes
     *
     * @param $circuit
     * @param bool $return
     * @return int|string
     */
    static public function showVoltages($circuit, $return = false)
    {
        $text = "Node voltages:\n\n";

        foreach ($circuit->V as $k => $v)
        {
            $text .= 'V(' . $circuit->getNodeName($k) . ') = ' . number_format($v, 6) . " V\n";
        }

        $text .= "\n";

        return $return ? $text : print($text);
    }
}