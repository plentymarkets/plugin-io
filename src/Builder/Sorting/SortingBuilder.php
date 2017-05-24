<?php

namespace IO\Builder\Sorting;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Item\Search\Sort\NameSorting;
use IO\Services\SessionStorageService;

class SortingBuilder
{
    public static function buildSorting($sortingString)
    {
        $sortingParameter =  self::filterSortingString($sortingString);

        if(strpos($sortingString, 'texts.name') !== false)
        {
            $sortingInterface = pluginApp(NameSorting::class, [self::buildNameSorting($sortingParameter["sortingPath"]), pluginApp(SessionStorageService::class)->getLang(), $sortingParameter["sortingOrder"]]);
        }
        else
        {
            $sortingInterface = pluginApp(MultipleSorting::class);
            $sortingInterface->add($sortingParameter["sortingPath"], $sortingParameter["sortingOrder"]);
        }

        return $sortingInterface;
    }

    public static function buildDefaultSorting()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $usedSortingPriority1 = $templateConfigService->get('sorting.priority1');
        $usedSortingPriority2 = $templateConfigService->get('sorting.priority2');
        $usedSortingPriority3 = $templateConfigService->get('sorting.priority3');

        if($usedSortingPriority2 == 'item.notSelected' && $usedSortingPriority3 == 'item.notSelected')
        {
            //SingleSort
            $sortingParameter1 = self::filterSortingString($usedSortingPriority1);
            $sortingInterface = pluginApp(SingleSorting::class);

        }
        else
        {
            //MultiSort
            $sortingInterface = pluginApp(MultipleSorting::class);

            $sortingParameter1 = self::filterSortingString($usedSortingPriority1);
            $sortingInterface->add($sortingParameter1["sortingPath"], $sortingParameter1["sortingOrder"]);
            if($usedSortingPriority2 != 'item.notSelected')
            {
                $sortingParameter2 = self::filterSortingString($usedSortingPriority2);
                $sortingInterface->add($sortingParameter2["sortingPath"], $sortingParameter2["sortingOrder"]);
            }
            if($usedSortingPriority3 != 'item.notSelected')
            {
                $sortingParameter3 = self::filterSortingString($usedSortingPriority3);
                $sortingInterface->add($sortingParameter3["sortingPath"], $sortingParameter3["sortingOrder"]);
            }
        }

        return $sortingInterface;
    }

    private static function filterSortingString($sortingString)
    {
        $e = explode('_', $sortingString);
        $sortingOrder = $e[count($e)-1];
        $sortingPath  = str_replace('_'.$sortingOrder, '', $sortingString);

        return $sortingParameter = [
            "sortingOrder" => $sortingOrder,
            "sortingPath"  => $sortingPath
        ];
    }

    public static function buildNameSorting($path)
    {
        return str_replace('texts.', '', $path);
    }
}