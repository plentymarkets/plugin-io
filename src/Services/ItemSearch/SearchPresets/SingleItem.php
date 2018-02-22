<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\TemplateConfigService;

class SingleItem implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withUrls()
            ->withPrices()
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

        return $searchFactory;
    }
}