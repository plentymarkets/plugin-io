<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class VariationAttributeMap
 * @package IO\Services\ItemSearch\SearchPresets
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationAttributeMap
 */
class VariationAttributeMap implements SearchPreset
{
    /**
     * @inheritDoc
     */
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );

        $searchFactory->withResultFields(
            ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_VARIATION_ATTRIBUTE_MAP )
        );

        $searchFactory
            ->withVariationAttributeMap()
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withReducedResults()
            ->withLinkToContent();

        if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
        {
            $searchFactory->hasItemId($options['itemId']);
        }

        return $searchFactory;
    }
}
