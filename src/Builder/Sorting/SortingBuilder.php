<?php

namespace IO\Builder\Sorting;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\BaseSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Item\Search\Sort\NameSorting;
use IO\Services\SessionStorageService;
use Plenty\Modules\Item\Search\Filter\TextFilter;

class SortingBuilder
{
    public static function buildSorting($sortingString)
    {
        $sortingParameter =  self::filterSortingString($sortingString);

        if(strpos($sortingString, 'texts.name') !== false)
        {
            $singleSortingInterface = pluginApp(NameSorting::class, [self::buildNameSorting($sortingParameter["sortingPath"]), pluginApp(SessionStorageService::class)->getLang(), $sortingParameter["sortingOrder"]]);
        }
        else if($sortingString == "item.score")
        {
            $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'DESC']);
        }
        else
        {
            $singleSortingInterface = pluginApp(SingleSorting::class,[$sortingParameter["sortingPath"], $sortingParameter["sortingOrder"]]);
        }

        return $singleSortingInterface;
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

        if($usedSortingPrioritySearch2 == 'notSelected' && $usedSortingPrioritySearch3 == 'notSelected')
        {
            //SingleSort
            if($usedSortingPrioritySearch1 == 'item.score')
            {
                $sortingInterface = pluginApp(SingleSorting::class,['_score', 'DESC']);
            }
            else
            {
                $sortingInterface = self::filterSortingInterface($usedSortingPrioritySearch1);
            }
        }
        else
        {
            //MultiSort
            $sortingInterface = pluginApp(MultipleSorting::class);

            if($usedSortingPrioritySearch1 == 'item.score')
            {
                $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'DESC']);
            }
            else
            {
                $singleSortingInterface = self::filterSortingInterface($usedSortingPrioritySearch1);
            }
            $sortingInterface->addSorting($singleSortingInterface);

            if($usedSortingPrioritySearch2 != 'notSelected')
            {
                if($usedSortingPrioritySearch2 == 'item.score')
                {
                    $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'DESC']);
                }
                else
                {
                    $singleSortingInterface = self::filterSortingInterface($usedSortingPrioritySearch2);
                }
                $sortingInterface->addSorting($singleSortingInterface);
            }
            if($usedSortingPrioritySearch3 != 'notSelected')
            {
                if($usedSortingPrioritySearch3 == 'item.score')
                {
                    $singleSortingInterface = pluginApp(SingleSorting::class,['_score', 'DESC']);
                }
                else
                {
                    $singleSortingInterface = self::filterSortingInterface($usedSortingPrioritySearch3);
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

        if($usedSortingPriorityCategory2 == 'notSelected' && $usedSortingPriorityCategory3 == 'notSelected')
        {
            //SingleSort
            $sortingInterface = self::filterSortingInterface($usedSortingPriorityCategory1);
        }
        else
        {
            //MultiSort
            $sortingInterface = pluginApp(MultipleSorting::class);

            $singleSortingInterface = self::filterSortingInterface($usedSortingPriorityCategory1);
            $sortingInterface->addSorting($singleSortingInterface);

            if($usedSortingPriorityCategory2 != 'notSelected')
            {
                $singleSortingInterface = self::filterSortingInterface($usedSortingPriorityCategory2);

                $sortingInterface->addSorting($singleSortingInterface);
            }
            if($usedSortingPriorityCategory3 != 'notSelected')
            {
                $singleSortingInterface = self::filterSortingInterface($usedSortingPriorityCategory3);

                $sortingInterface->addSorting($singleSortingInterface);
            }
        }

        return $sortingInterface;
    }

    private static function filterSortingInterface($usedSortingPriority)
    {
        $sortingParameter1 = self::filterSortingString($usedSortingPriority);
        if(strpos($sortingParameter1["sortingPath"], 'texts.name') !== false)
        {
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $usedItemName = $templateConfigService->get('item.name');

            if(strlen($usedItemName))
            {
                if($usedItemName == '0')
                {
                    $textFilterType = "texts.name1";
                }
                elseif($usedItemName == '1')
                {
                    $textFilterType = "texts.name2";
                }
                elseif($usedItemName == '2')
                {
                    $textFilterType = "texts.name3";
                }
            }
            return $sortingInterface = pluginApp(NameSorting::class, [self::buildNameSorting($textFilterType), pluginApp(SessionStorageService::class)->getLang(), $sortingParameter1["sortingOrder"]]);
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