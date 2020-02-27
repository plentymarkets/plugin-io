<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

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
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\SingleItem
 */
class SingleItem implements SearchPreset
{
    /**
     * @inheritDoc
     */
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );

        $searchFactory->withResultFields(
            ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_SINGLE_ITEM )
        );

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withPropertyGroups()
            ->withOrderPropertySelectionValues()
            ->withVariationProperties()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->withBundleComponents()
            ->withAvailability()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withLinkToContent()
            ->withReducedResults()
            ->withTags()
            ->setPage(1, 1);

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
            $searchFactory->groupByTemplateConfig();
        }

        if ( array_key_exists( 'setCategory', $options ) && $options['setCategory'] === true )
        {
            $searchFactory->withCurrentCategory();
        }

        return $searchFactory;
    }
}
