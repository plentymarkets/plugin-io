<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeNumber
{
    protected function number($min = 0, $max = 999999)
    {
        if ( $max < $min )
        {
            $max = 999999;
        }

        return rand($min, $max);
    }

    protected function float($min = 0, $max = 9999, $numberOfDecimals = 2)
    {
        $digit    = rand($min + 1, $max);
        $tmp      = (rand() / rand());
        $fraction = $tmp - ($tmp % 10);
        $result   = intval((($digit - 1) + $fraction) * $this->pow(10, $numberOfDecimals));

        return $result / $this->pow(10, $numberOfDecimals);
    }

    protected function percentage()
    {
        return $this->float(0, 100);
    }

    private function pow($base, $exp)
    {
        $result = 1;
        while($exp > 0)
        {
            $result *= $base;
            $exp--;
        }

        while($exp < 0)
        {
            $result /= $base;
            $exp++;
        }

        return $result;
    }
}