<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\TemplateConfigService;

/**
 * Class SingleItem
 *
 * Search preset for single items
 * Available options:
 * - itemId:        Item id to get
 * - variationId:   Variation id to get. If not defined, plugin configuration will be considered if main or child variation should be displayed.
 * - setCategory:   Flag indicating if item should be set as current item to be displayed in breadcrumbs
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class SingleItem implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class )
            ->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_SINGLE_ITEM )
            );

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withPropertyGroups()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer();

        if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
        {
            $searchFactory->hasItemId($options['itemId']);
        }

        if(array_key_exists('variationId', $options) && $options['variationId'] != 0)
        {
            $searchFactory->hasVariationId($options['variationId']);
        }
        else
        {
            $templateConfigService = pluginApp( TemplateConfigService::class );
            $variationShowType = $templateConfigService->get('item.variation_show_type');
            if($variationShowType == 'main')
            {
                $searchFactory->isMain();
            }
            elseif($variationShowType == 'child')
            {
                $searchFactory->isChild();
            }
        }

        if ( array_key_exists( 'setCategory', $options ) && $options['setCategory'] === true )
        {
            $searchFactory->withCurrentCategory();
        }

        return $searchFactory;
    }
}