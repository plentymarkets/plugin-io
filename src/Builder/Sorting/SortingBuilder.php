<?php

namespace IO\Builder\Sorting;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\BaseSorting;
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
        else if($sortingString == "item.score")
        {
            $sortingInterface = pluginApp(MultipleSorting::class);
            $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'ASC']);

            $sortingInterface->addSorting($singleSortingInterface);
        }
        else
        {
            $sortingInterface = pluginApp(MultipleSorting::class);
            $singleSortingInterface = pluginApp(SingleSorting::class,[$sortingParameter["sortingPath"], $sortingParameter["sortingOrder"]]);

            $sortingInterface->addSorting($singleSortingInterface);
        }

        return $sortingInterface;
    }


    public static function buildDefaultSortingSearch()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        $usedSortingPrioritySearch1 = $templateConfigService->get('sorting.prioritySearch1');
        $usedSortingPrioritySearch2 = $templateConfigService->get('sorting.prioritySearch2');
        $usedSortingPrioritySearch3 = $templateConfigService->get('sorting.prioritySearch3');

        if($usedSortingPrioritySearch2 == 'item.notSelected' && $usedSortingPrioritySearch3 == 'item.notSelected')
        {
            //SingleSort
            if($usedSortingPrioritySearch1 == 'item.score')
            {
                $sortingInterface = pluginApp(SingleSorting::class,['_score', 'ASC']);
            }
            else
            {
                $sortingInterface = self::SortingPriority($usedSortingPrioritySearch1);
            }
        }
        else
        {
            //MultiSort
            $sortingInterface = pluginApp(MultipleSorting::class);

            if($usedSortingPrioritySearch1 == 'item.score')
            {
                $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'ASC']);
            }
            else
            {
                $singleSortingInterface = self::SortingPriority($usedSortingPrioritySearch1);
            }
            $sortingInterface->addSorting($singleSortingInterface);

            if($usedSortingPrioritySearch2 != 'item.notSelected')
            {
                if($usedSortingPrioritySearch2 == 'item.score')
                {
                    $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'ASC']);
                }
                else
                {
                    $singleSortingInterface = self::SortingPriority($usedSortingPrioritySearch2);
                }
                $sortingInterface->addSorting($singleSortingInterface);
            }
            if($usedSortingPrioritySearch3 != 'item.notSelected')
            {
                if($usedSortingPrioritySearch3 == 'item.score')
                {
                    $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'ASC']);
                }
                else
                {
                    $singleSortingInterface = self::SortingPriority($usedSortingPrioritySearch3);
                }

                $sortingInterface->addSorting($singleSortingInterface);
            }
        }
        return $sortingInterface;
    }

    public static function buildDefaultSortingCategory()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        $usedSortingPriorityCategory1 = $templateConfigService->get('sorting.priorityCategory1');
        $usedSortingPriorityCategory2 = $templateConfigService->get('sorting.priorityCategory2');
        $usedSortingPriorityCategory3 = $templateConfigService->get('sorting.priorityCategory3');

        if($usedSortingPriorityCategory2 == 'item.notSelected' && $usedSortingPriorityCategory3 == 'item.notSelected')
        {
            //SingleSort
            $sortingInterface = self::SortingPriority($usedSortingPriorityCategory1);
        }
        else
        {
            //MultiSort
            $sortingInterface = pluginApp(MultipleSorting::class);

            $singleSortingInterface = self::SortingPriority($usedSortingPriorityCategory1);
            $sortingInterface->addSorting($singleSortingInterface);

            if($usedSortingPriorityCategory2 != 'item.notSelected')
            {
                $singleSortingInterface = self::SortingPriority($usedSortingPriorityCategory2);

                $sortingInterface->addSorting($singleSortingInterface);
            }
            if($usedSortingPriorityCategory3 != 'item.notSelected')
            {
                $singleSortingInterface = self::SortingPriority($usedSortingPriorityCategory3);

                $sortingInterface->addSorting($singleSortingInterface);
            }
        }

        return $sortingInterface;
    }

    private static function SortingPriority($usedSortingPriority)
    {
        $sortingParameter1 = self::filterSortingString($usedSortingPriority);
        if(strpos($sortingParameter1["sortingPath"], 'texts.name') != false)
        {
            return $sortingInterface = pluginApp(NameSorting::class, [self::buildNameSorting($sortingParameter1["sortingPath"]), pluginApp(SessionStorageService::class)->getLang(), $sortingParameter1["sortingOrder"]]);
        }
        else
        {
            return $sortingInterface = pluginApp(SingleSorting::class,[$sortingParameter1["sortingPath"], $sortingParameter1["sortingOrder"]]);
        }
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