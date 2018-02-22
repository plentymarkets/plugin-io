<?php

namespace IO\Services\ItemSearch\Helper;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\TemplateConfigService;

class SortingHelper
{
    public static function getSorting( $sortingConfig = null, $isCategory = true )
    {
        $sortings = [];
        if ( $sortingConfig === 'default.recommended_sorting' || !strlen( $sortingConfig ) )
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp( TemplateConfigService::class );
            $configKeyPrefix = $isCategory ? 'sorting.priorityCategory' : 'sorting.prioritySearch';

            foreach( [1,2,3] as $priority )
            {
                $defaultSortingValue = $templateConfigService->get($configKeyPrefix . $priority );
                if ( $defaultSortingValue !== 'notSelected' )
                {
                    $defaultSorting = self::getSorting( $defaultSortingValue, $isCategory );
                    $sortings[] = $defaultSorting[0];
                }
            }
        }
        else
        {
            list($sortingField, $sortingOrder) = explode('_', $sortingConfig );
            if ( $sortingField === 'item.score' )
            {
                $sortingField = '_score';
                $sortingOrder = BaseSearchFactory::SORTING_ORDER_DESC;
            }

            $sortings[] = ['field' => $sortingField, 'order' => $sortingOrder];
        }

        return $sortings;
    }

    public static function getCategorySorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, true );
    }

    public static function getSearchSorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, false );
    }
}