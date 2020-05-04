<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class LiveShoppingItems
 * @package IO\Services\ItemSearch\SearchPresets
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\LiveShoppingItems
 */
class LiveShoppingItems implements SearchPreset
{
    /**
     * @inheritDoc
     */
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp(VariationSearchFactory::class);

        if (isset($options['resultFields']) && count($options['resultFields'])) {
            $searchFactory->withResultFields($options['resultFields']);
        } else {
            $searchFactory->withResultFields(
                array_merge(
                    ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_LIST_ITEM),
                    ['stock.net', 'variation.stockLimitation']
                )
            );
        }

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withPropertyGroups()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->withBundleComponents()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withLinkToContent()
            ->withReducedResults();

        if (array_key_exists('itemId', $options) && $options['itemId'] != 0) {
            $searchFactory->hasItemId($options['itemId']);
        }

        if (array_key_exists('itemIds', $options) && count($options['itemIds'])) {
            $searchFactory->hasItemIds($options['itemIds']);
        }

        if (array_key_exists('sorting', $options) && count($options['sorting'])) {
            $searchFactory->sortBy($options['sorting']['path'], $options['sorting']['order']);
        }


        return $searchFactory;
    }
}
