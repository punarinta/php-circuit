<?php

require_once 'Matrix.php';
require_once 'Element.php';

class Circuit
{
    public $G;
    public $I;
    public $V;
    public $elements;
    public $nodes;

    /**
     * Prepares G and I matrices
     *
     * @param int $f
     */
    public function prepare($f = 0)
    {
        $n = count($this->nodes);

        for ($i = 0; $i < $n; $i++)
        {
            $this->I[$i] = 0;
            foreach ($this->elements as $e)
            {
                if ($e->I)
                {
                    if ($e->pins[0] == $i+1) $this->I[$i] -= $e->I;
                    if ($e->pins[1] == $i+1) $this->I[$i] += $e->I;
                }
            }

            for ($j = 0; $j < $n; $j++)
            {
                $this->G[$i][$j] = 0;

                if ($i === $j)
                {
                    // compute sum of all the nearby conductances
                    foreach ($this->elements as $e)
                    {
                        if ($e->pins[0] == $i+1 || $e->pins[1] == $i+1 || $e->pins[0] == $j+1 || $e->pins[1] == $j+1)
                        {
                            $this->G[$i][$i] += $e->g($f);
                        }
                    }
                }
                else
                {
                    // find elements between these two points
                    foreach ($this->elements as $e)
                    {
                        if (!$e->pins[0] || !$e->pins[1])
                        {
                            continue;
                        }
                        if ($e->pins[0] == $i+1 && $e->pins[1] == $j+1 || $e->pins[1] == $i+1 && $e->pins[0] == $j+1)
                        {
                            $this->G[$i][$j] -= $e->g($f);
                        }
                    }
                }
            }
        }
    }

    /**
     * Solves G x E = J matrix equation
     */
    public function solve()
    {
        $n = count($this->nodes);
        $iG = Matrix::invert($this->G);

        for ($i = 0; $i < $n; $i++)
        {
            $this->V[$i] = 0;
            for ($j = 0; $j < $n; $j++)
            {
                $this->V[$i] += $iG[$i][$j] * $this->I[$j];
            }
        }
    }

    /**
     * Memorize nodes and count their usage
     *
     * @param $names
     * @return array
     */
    public function pushNodes($names)
    {
        $ids = [];

        foreach ($names as $name)
        {
            if (!$name || strtoupper($name) === 'GND')
            {
                $ids[] = 0;
                continue;
            }
            if (!isset ($this->nodes[$name]))
            {
                $this->nodes[$name] = count($this->nodes) + 1;
            }
            $ids[] = $this->nodes[$name];
        }

        return $ids;
    }

    /**
     * Returns node's literal name by its internal id
     *
     * @param $id
     * @return mixed
     */
    public function getNodeName($id)
    {
        return array_keys($this->nodes, $id + 1)[0];
    }

    /**
     * Imports schematics from SPICE data
     *
     * @param $code
     */
    public function fromSpice($code)
    {
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

            switch (strtoupper($w[0][0]))
            {
                case 'R';
                    $e = new Element($w[0], Element::TYPE_RESISTOR, $w[3]);
                    $e->pins = $this->pushNodes([$w[1], $w[2]]);
                    $this->elements[] = $e;
                    break;

                case 'C';
                    $e = new Element($w[0], Element::TYPE_CAPACITOR, $w[3]);
                    $e->pins = $this->pushNodes([$w[1], $w[2]]);
                    $this->elements[] = $e;
                    break;

                case 'L';
                    $e = new Element($w[0], Element::TYPE_INDUCTOR, $w[3]);
                    $e->pins = $this->pushNodes([$w[1], $w[2]]);
                    $this->elements[] = $e;
                    break;

                case 'I';
                    $e = new Element($w[0], Element::TYPE_CURRENT, $w[4]);
                    $e->pins = $this->pushNodes([$w[1], $w[2]]);
                    $this->elements[] = $e;
                    break;

                case 'V';
                    $e = new Element($w[0], Element::TYPE_VOLTAGE, $w[4]);
                    $e->pins = $this->pushNodes([$w[1], $w[2]]);
                    $this->elements[] = $e;
                    break;
            }
        }
    }
}