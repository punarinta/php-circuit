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

    public $f;
    public $t;

    /**
     * Prepares G and I matrices
     *
     * @param int $dt
     */
    public function prepare($dt = 0)
    {
        if ($dt)
        {
            // that's a transient mode, so do some preparations

            foreach ($this->elements as $k => $e)
            {
                if ($e->type == Element::CAPACITOR)
                {
                    $this->elements[$k]->I = -$e->C * (($e->pins[0] ? $this->V[$e->pins[0] - 1] : 0) - ($e->pins[1] ? $this->V[$e->pins[1] - 1] : 0)) / $dt;
                }
            }
        }

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
                            $this->G[$i][$i] += $dt ? $e->gt($dt) : $e->g($this->f);
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
                            $this->G[$i][$j] -= $dt ? $e->gt($dt) : $e->g($this->f);
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
     * Finds an element by its name
     *
     * @param $name
     * @return null
     */
    public function findElementByName($name)
    {
        foreach ($this->elements as $e)
        {
            if (!strcasecmp($e->name, $name)) return $e;
        }

        return null;
    }

    /**
     * Finds a node by its name
     *
     * @param $name
     * @return null
     */
    public function findNodeByName($name)
    {
        foreach ($this->nodes as $n => $v)
        {
            if (!strcasecmp($n, $name)) return $v;
        }

        return null;
    }
}