<?php

namespace IO\Services\ItemSearch\Helper;

abstract class VariationSearchResultAbstractFaker
{
    public $isList = false;
    public $listRange = [];

    public abstract function generate();
}