<?php

namespace IO\Services\VdiSearch\Helper;

use IO\Contracts\SortingContract;
use IO\Contracts\VariationSearchFactoryContract;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Data\Update\Handler\Traits\MultilingualTrait;

class SortingHelper implements SortingContract
{
    use MultilingualTrait;

    const SORTING_MAP = [
        'item.id'                            => 'filter.itemId',
        'texts.name1'                        => 'analyzed.multilingual.{{lang}}.name1.sorting',
        'texts.name2'                        => 'analyzed.multilingual.{{lang}}.name2.sorting',
        'texts.name3'                        => 'analyzed.multilingual.{{lang}}.name3.sorting',
        'variation.createdAt'                => 'filter.timestamps.createdAt',
        'variation.updatedAt'                => 'filter.timestamps.updatedAt',
        'variation.id'                       => 'variationId',
        'variation.number'                   => 'analyzed.number.sorting',
        'variation.availability.averageDays' => 'filter.availabilityAverageDays',
        'variation.position'                 => 'filter.position',
        'item.manufacturer.externalName'     => 'analyzed.externalManufacturer.sorting',
        'item.manufacturer.position'         => 'analyzed.manufacturer.sorting',
        'stock.net'                          => 'filter.stock.net',
        'sorting.price.avg'                  =>'filter.prices.price'
    ];

    /**
     * Get sorting values from plugin configuration
     *
     * @param string    $sortingConfig  The configuration value from plugin
     * @param bool      $isCategory     Get default sorting configuration for category or for search
     *
     * @return array
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
                $sortingOrder = VariationSearchFactoryContract::SORTING_ORDER_DESC;
            }

            else if ( $sortingField === 'texts.name' )
            {
                $sortingField = self::getUsedItemName();
            }

            //TODO VDI MEYER
            $sortings[] = [
                'field' => str_replace('{{lang}}', $this->getM10lByLanguage(pluginApp(SessionStorageService::class)->getLang()), self::SORTING_MAP[$sortingField]),
                'order' => $sortingOrder
            ];
        }

        return $sortings;
    }

    /**
     * Get sorting values for categories from config
     *
     * @param string $sortingConfig     The configuration value
     * @return array
     */
    public function getCategorySorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, true );
    }

    /**
     * Get sorting values for searches from config
     *
     * @param string $sortingConfig     The configuration value
     * @return array
     */
    public function getSearchSorting( $sortingConfig = null )
    {
        return self::getSorting( $sortingConfig, false );
    }

    /**
     * @return string
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
