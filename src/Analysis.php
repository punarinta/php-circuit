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
                case Element::CURRENT:
                    $element->I = $X;
                    break;

                case Element::VOLTAGE:
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

        if ($type === 'DEC' || $type === 'OCT')
        {
            $m = pow($type === 'DEC' ? 10 : 8, 1 / $np);

            for ($X = $start; $X <= $stop; $X *= $m)
            {
                $circuit->f = $X;
                self::dcop($circuit);
                $ops[(string)$X] = $circuit->V;
            }
        }
        else if ($type === 'LIN')
        {
            $m = ($stop - $start) / $np;
            for ($X = $start; $X <= $stop; $X += $m)
            {
                $circuit->f = $X;
                self::dcop($circuit);
                $ops[(string)$X] = $circuit->V;
            }
        }
        else
        {
            throw new \Exception('Unknown variation mode for AC analysis.');
        }

        return $ops;
    }

    /**
     * Sweeps time
     *
     * @param $circuit
     * @param $step
     * @param $stop
     * @param int $start
     * @return array
     * @throws Exception
     */
    static public function tran($circuit, $step, $stop, $start = 0)
    {
        $ops = [];

        // find operating point first
        // self::dcop($circuit);

        for ($t = $start; $t <= $stop; $t += $step)
        {
            $circuit->t = $t;

        /*    echo 'V1 '; print_r($circuit->V);
            echo 'I1 '; print_r($circuit->I);*/

            $circuit->prepare($step);
            $circuit->solve();

            $ops[(string)$t] = $circuit->V;

        /*   echo 'V2 '; print_r($circuit->V);
            echo 'I2 '; print_r($circuit->I);
            echo "\n\n\n";*/
        }

        //print_r($ops);

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