<?php

namespace PhpCircuit;

const FEMTO     = 1e-15;
const PICO      = 1e-12;
const NANO      = 1e-9;
const MICRO     = 1e-6;
const MILLI     = 1e-3;
const KILO      = 1e3;
const MEGA      = 1e6;
const GIGA      = 1e9;
const TERA      = 1e12;

class Format
{
    /**
     * Converts a scientifically notated string to a floating point value
     *
     * @param $str
     * @return float
     */
    static public function toFloat($str)
    {
        return strtr(strtolower($str), array
        (
            't'     => 'e12',
            'g'     => 'e9',
            'meg'   => 'e6',
            'k'     => 'e3',
            'm'     => 'e-3',
            'u'     => 'e-6',
            'n'     => 'e-9',
            'p'     => 'e-12',
            'f'     => 'e-15',
        ));
    }

    /**
     * Converts a floating point value to a scientifically notated string
     *
     * @param $f
     * @return string
     */
    static public function toString($f)
    {
        if ($f == 0) return '0';

        $minus = $f < 0 ? '-' : '';

        $f = abs($f);

        if ($f < PICO)  return $minus . number_format($f / FEMTO, 3) . 'f';
        if ($f < NANO)  return $minus . number_format($f / PICO, 3) . 'p';
        if ($f < MICRO) return $minus . number_format($f / NANO, 3) . 'n';
        if ($f < MILLI) return $minus . number_format($f / MICRO, 3) . 'u';
        if ($f < 1)     return $minus . number_format($f / MILLI, 3) . 'm';
        if ($f < KILO)  return $minus . number_format($f, 3);
        if ($f < MEGA)  return $minus . number_format($f / KILO, 3) . 'k';
        if ($f < GIGA)  return $minus . number_format($f / MEGA, 3) . 'meg';
        if ($f < TERA)  return $minus . number_format($f / GIGA, 3) . 'g';
        else            return $minus . number_format($f / TERA, 3) . 't';
    }
}