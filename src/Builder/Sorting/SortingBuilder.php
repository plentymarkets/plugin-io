<?php

namespace IO\Builder\Sorting;

use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;

class SortingBuilder
{
    public static function buildSorting($sortingString)
    {
        $e = explode('_', $sortingString);
        
        $sortingOrder = $e[count($e)-1];
        $sortingPath  = str_replace('_'.$sortingOrder, '', $sortingString);
        
        return [
            'path'  => $sortingPath,
            'order' => $sortingOrder
        ];
    }
}