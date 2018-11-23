<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\TemplateConfigService;


class LiveShoppingItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        
        if(isset($options['resultFields']) && count($options['resultFields']))
        {
            $searchFactory->withResultFields($options['resultFields']);
        }
        else
        {
            $searchFactory->withResultFields(
                ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_LIST_ITEM )
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
            ->withLinkToContent();
        
        if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
        {
            $searchFactory->hasItemId($options['itemId']);
        }
    
        if(array_key_exists('itemIds', $options) && count($options['itemIds']))
        {
            $searchFactory->hasItemIds($options['itemIds']);
        }
    
        if(array_key_exists('sorting', $options) && count($options['sorting']))
        {
            $searchFactory->sortBy($options['sorting']['path'], $options['sorting']['order']);
        }
        
        
        return $searchFactory;
    }
}