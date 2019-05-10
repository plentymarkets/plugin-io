<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeNumber
{
    protected function number($min = 0, $max = 999999)
    {
        if ( $max < $min )
        {
            return rand($min);
        }

        return rand($min, $max);
    }

    protected function float($min = 0, $max = 9999, $numberOfDecimals = 2)
    {
        $digit    = rand($min + 1, $max);
        $tmp      = (rand() / rand());
        $fraction = $tmp - ($tmp % 10);
        $result   = intval((($digit - 1) + $fraction) * pow(10, $numberOfDecimals));

        return $result / pow(10, $numberOfDecimals);
    }

    protected function percentage()
    {
        return $this->float(0, 100);
    }
}