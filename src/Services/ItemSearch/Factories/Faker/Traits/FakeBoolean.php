<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeBoolean
{
    protected function boolean()
    {
        return random_int(0, mt_getrandmax()) % 2 === 0;
    }
}