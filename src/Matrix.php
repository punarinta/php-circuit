<?php

class Matrix
{
    /**
     * @param $w
     * @param null $h
     * @return array
     */
    static public function create($w, $h = null)
    {
        $A = [];

        if (!$h) $h = $w;

        for ($i = 0; $i < $h; $i++)
        {
            $A[$i] = array_fill(0, $w, 0.0);
        }

        return $A;
    }

    /**
     * @param $A
     * @return mixed
     */
    static public function randomize($A)
    {
        foreach ($A as $k1 => $v1)
        {
            foreach ($v1 as $k2 => $v2)
            {
                $A[$k1][$k2] = mt_rand(0, 100);
                usleep(1000);
            }
        }

        return $A;
    }

    /**
     * @param $A
     * @return string
     */
    static public function show($A)
    {
        $text = '';
        foreach ($A as $v1) $text .= implode(' ', $v1) . "\n";
        return $text;
    }

    /**
     * @param $A
     * @return array
     */
    static public function invert($A)
    {
        $I = [];
        $n = count($A);

        // get identity matrix
        for ($i = 0; $i < $n; ++$i)
        {
            for ($j = 0; $j < $n; ++$j)
            {
                $I[$i][$j] = ($i == $j) ? 1 : 0;
            }
        }

        // append identity matrix
        for ($i = 0; $i < $n; ++$i)
        {
            $A[$i] = array_merge($A[$i], $I[$i]);
        }

        // forward run
        for ($j = 0; $j < $n - 1; ++$j)
        {
            // for all remaining rows (diagonally)
            for ($i = $j + 1; $i < $n; ++$i)
            {
                // if the value is not already 0
                if ($A[$i][$j] !== 0)
                {
                    // adjust scale to pivot row
                    // subtract pivot row from current
                    $scalar = $A[$j][$j] / $A[$i][$j];
                    for ($jj = $j; $jj < $n * 2; ++$jj)
                    {
                        $A[$i][$jj] *= $scalar;
                        $A[$i][$jj] -= $A[$j][$jj];
                    }
                }
            }
        }

        // reverse run
        for ($j = $n - 1; $j > 0; --$j)
        {
            for ($i = $j - 1; $i >= 0; --$i)
            {
                if ($A[$i][$j] !== 0)
                {
                    if (!$A[$i][$j]) continue;

                    $scalar = $A[$j][$j] / $A[$i][$j];
                    for ($jj = $i; $jj < $n * 2; ++$jj)
                    {
                        $A[$i][$jj] *= $scalar;
                        $A[$i][$jj] -= $A[$j][$jj];
                    }
                }
            }
        }

        // last run to make all diagonal 1s
        for ($j = 0; $j < $n; ++$j)
        {
            if ($A[$j][$j] !== 1)
            {
                if (!$A[$j][$j]) continue;

                $scalar = 1 / $A[$j][$j];
                for ($jj = $j; $jj < $n * 2; ++$jj)
                {
                    $A[$j][$jj] *= $scalar;
                }

                $I[$j] = array_slice($A[$j], $n);
            }
        }

        return $I;
    }
}
