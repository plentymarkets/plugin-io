<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeDate
{
    protected function timestamp()
    {
        return rand(0, time());
    }

    protected function dateString($format = "Y-m-d H:i:s")
    {
        return date($format, $this->timestamp());
    }
}