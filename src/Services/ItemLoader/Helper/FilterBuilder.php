<?php

namespace IO\Services\ItemLoader\Helper;

interface FilterBuilder
{
    public function getFilters($options):array;
}