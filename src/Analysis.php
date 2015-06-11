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
     * Sweeps a DC source
     *
     * @param $circuit
     * @param $source
     * @param $start
     * @param $stop
     * @param $step
     * @return array
     * @throws Exception
     */
    static public function dc($circuit, $source, $start, $stop, $step)
    {
        $ops = [];
        $circuit->f = 0;

        for ($X = $start; $X <= $stop; $X += $step)
        {
            if (!$element = $circuit->findElementByName($source))
            {
                throw new \Exception("Element '$source' not found.");
            }

            switch ($element->type)
            {
                case Element::TYPE_CURRENT:
                    $element->I = $X;
                    break;

                case Element::TYPE_VOLTAGE:
                    $element->setVoltage($X);
                    break;

                default:
                    throw new \Exception("Element '$source' is not a source.");
            }

            self::dcop($circuit);
            $ops[(string)$X] = $circuit->V;
        }

        return $ops;
    }

    /**
     * Sweeps frequency
     *
     * @param $circuit
     * @param $type
     * @param $np
     * @param $start
     * @param $stop
     * @return array
     * @throws Exception
     */
    static public function ac($circuit, $type, $np, $start, $stop)
    {
        $ops = [];

        // support only decade variation for now
        if ($type !== 'DEC')
        {
            throw new \Exception('Only decade variation mode is supported for AC analysis.');
        }

        $m = pow(10, 1 / $np);

        for ($X = $start; $X <= $stop; $X *= $m)
        {
            $circuit->f = $X;
            self::dcop($circuit);
            $ops[(string)$X] = $circuit->V;
        }

        return $ops;
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
        $text = "Operating point:\n\n";

        foreach ($circuit->V as $k => $v)
        {
            $text .= 'V(' . $circuit->getNodeName($k) . ") \t\t = " . number_format($v, 6) . " V\n";
        }

        return $return ? $text : print($text);
    }
}