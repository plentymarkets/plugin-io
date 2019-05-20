<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeBoolean
{
    protected function boolean()
    {
        return rand() % 2 === 0;
    }
}