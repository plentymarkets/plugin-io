<?php

namespace IO\Services\ItemSearch\Helper;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class SortingHelper
 * Generate sorting values from plugin configuration.
 * @package IO\Services\ItemSearch\Helper
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper
 */
class SortingHelper
{
    /**
     * Get sorting values from plugin configuration
     * @param string    $sortingConfig  The configuration value from plugin
     * @param bool      $isCategory     Get default sorting configuration for category or for search
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper::getSorting()
     */
    public function getSorting( $sortingConfig = null, $isCategory = true )
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
                $sortingOrder = VariationSearchFactory::SORTING_ORDER_DESC;
            }

            else if ( $sortingField === 'texts.name' )
            {
                $sortingField = self::getUsedItemName();
            }

            $sortings[] = ['field' => $sortingField, 'order' => $sortingOrder];
        }

        return $sortings;
    }

    /**
     * Get sorting values for categories from config
     * @param string $sortingConfig     The configuration value
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper::getCategorySorting()
     */
    public function getCategorySorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, true );
    }

    /**
     * Get sorting values for searches from config
     * @param string $sortingConfig     The configuration value
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper::getSearchSorting()
     */
    public function getSearchSorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, false );
    }

    /**
     * @return string
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper::getUsedItemName()
     */
    public function getUsedItemName()
    {
        $templateConfigService = pluginApp(TemplateConfigService::class);

        $usedItemNameIndex = $templateConfigService->get('item.name');

        $usedItemName = [
            'texts.name1',
            'texts.name2',
            'texts.name3'
        ][$usedItemNameIndex];

        return $usedItemName;
    }

    /**
     * @param string $sorting
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper::splitPathAndOrder()
     */
    public function splitPathAndOrder($sorting)
    {
        $e = explode('_', $sorting);

        $sorting = [
            'path' => $e[0],
            'order'=> $e[1]
        ];

        if($sorting['path'] == 'texts.name')
        {
            $sorting['path'] = self::getUsedItemName();
        }

        return $sorting;
    }
}
