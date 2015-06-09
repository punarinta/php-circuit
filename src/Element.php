<?php

class Element
{
    const TYPE_RESISTOR     = 1;
    const TYPE_CAPACITOR    = 2;
    const TYPE_INDUCTOR     = 3;
    const TYPE_CURRENT      = 4;
    const TYPE_VOLTAGE      = 5;

    public $R;
    public $L;
    public $C;
    public $I;
    public $pins;

    public function __construct($type = self::TYPE_RESISTOR, $value = 1)
    {
        $this->R = 0;
        $this->L = 0;
        $this->R = 0;

        switch ($type)
        {
            case self::TYPE_RESISTOR:
                $this->R = $value;
                break;

            case self::TYPE_CAPACITOR:
                $this->C = $value;
                break;

            case self::TYPE_INDUCTOR:
                $this->L = $value;
                break;

            case self::TYPE_CURRENT:
                $this->R = 1e18;
                $this->I = $value;
                break;

            case self::TYPE_VOLTAGE:
                $this->R = 1e-18;
                $this->I = $value / 1e-18;
                break;
        }
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
            return 1 / $this->R;
        }

        $f *= M_PI * 2;

        return 1 / $this->R + $this->C * $f + $this->L ? (1 / ($this->L * $f)) : 0;
    }
}