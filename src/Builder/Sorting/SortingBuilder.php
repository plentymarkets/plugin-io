<?php

namespace IO\Builder\Sorting;

use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Item\Search\Sort\NameSorting;
use IO\Services\SessionStorageService;

class SortingBuilder
{
    public static function buildSorting($sortingString)
    {
        $e = explode('_', $sortingString);
        
        $sortingOrder = $e[count($e)-1];
        $sortingPath  = str_replace('_'.$sortingOrder, '', $sortingString);
    
        if(strpos($sortingString, 'texts.name') !== false)
        {
            $sortingInterface = pluginApp(NameSorting::class, [self::buildNameSorting($sortingPath), pluginApp(SessionStorageService::class)->getLang(), $sortingOrder]);
        }
        else
        {
            $sortingInterface = pluginApp(MultipleSorting::class);
            $sortingInterface->add($sortingPath, $sortingOrder);
        }
        
        return $sortingInterface;
    }
    
    public static function buildNameSorting($path)
    {
        return str_replace('texts.', '', $path);
    }
}