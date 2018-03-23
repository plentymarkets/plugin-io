<?php

namespace IO\Services\ItemSearch\Helper;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\TemplateConfigService;

/**
 * Class SortingHelper
 *
 * Generate sorting values from plugin configuration.
 *
 * @package IO\Services\ItemSearch\Helper
 */
class SortingHelper
{
    /**
     * Get sorting values from plugin configuration
     *
     * @param string    $sortingConfig  The configuration value from plugin
     * @param bool      $isCategory     Get default sorting configuration for category or for search
     *
     * @return array
     */
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

            else if ( $sortingField === 'texts.name' )
            {
                $templateConfigService = pluginApp(TemplateConfigService::class);
                $usedItemName = $templateConfigService->get('item.name');
                $sortingField = [
                    'texts.name1',
                    'texts.name2',
                    'texts.name3'
                ][$usedItemName];
            }

            $sortings[] = ['field' => $sortingField, 'order' => $sortingOrder];
        }

        return $sortings;
    }

    /**
     * Get sorting values for categories from config
     *
     * @param string $sortingConfig     The configuration value
     * @return array
     */
    public static function getCategorySorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, true );
    }

    /**
     * Get sorting values for searches from config
     *
     * @param string $sortingConfig     The configuration value
     * @return array
     */
    public static function getSearchSorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, false );
    }
}