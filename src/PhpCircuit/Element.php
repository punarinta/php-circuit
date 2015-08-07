<?php

namespace PhpCircuit;

class Element
{
    const RESISTOR     = 1;
    const CAPACITOR    = 2;
    const INDUCTOR     = 3;
    const CURRENT      = 4;
    const VOLTAGE      = 5;

    public $R;
    public $L;
    public $C;
    public $I;
    public $pins;
    public $name;
    public $type;

    /**
     * Creates and populates an element
     *
     * @param $name
     * @param int $type
     * @param int $value
     */
    public function __construct($name, $type = self::RESISTOR, $value = 1)
    {
        $this->R = 0;
        $this->L = 0;
        $this->C = 0;
        $this->I = 0;
        $this->type = $type;
        $this->name = $name;

        switch ($type)
        {
            case self::RESISTOR:
                $this->R = $value;
                break;

            case self::CAPACITOR:
                $this->C = $value;
                break;

            case self::INDUCTOR:
                $this->L = $value;
                break;

            case self::CURRENT:
                $this->R = 1e18;
                $this->I = $value;
                break;

            case self::VOLTAGE:
                $this->setVoltage($value);
                break;
        }
    }

    /**
     * Sets a voltage source within the element
     *
     * @param $V
     */
    public function setVoltage($V)
    {
        $this->R = 1e-18;
        $this->I = $V / 1e-18;
    }

    /**
     * Returns conductivity on a non-zero frequency
     *
     * @param $f
     * @return float
     */
    public function g($f)
    {
        if ($f <= 0)
        {
            return $this->R ? (1 / $this->R) : 0;
        }

        $f *= M_PI * 2;

        return $this->R ? (1 / $this->R) : 0 + $this->C * $f + ($this->L ? (1 / ($this->L * $f)) : 0);
    }

    /**
     * Returns conductivity for transient mode
     *
     * @param $dt
     * @return float
     */
    public function gt($dt)
    {
        if ($this->R)
        {
            return 1 / $this->R;
        }

        // TODO: support inductance

        return $this->C / $dt;
    }
}