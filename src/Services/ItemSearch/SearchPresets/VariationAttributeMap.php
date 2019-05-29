<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\TemplateConfigService;

/**
 * Class VariationAttributeMap
 * @package IO\Services\ItemSearch\SearchPresets
 */
class VariationAttributeMap implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        
        $searchFactory->withResultFields(
            ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_SINGLE_ITEM )
        );
        
        $searchFactory
            ->withAttributes()
            ->withLanguage()
            ->withUrls()
            ->isVisibleForClient()
            ->isActive()
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->withReducedResults()
            ->withLinkToContent();
    
        //TODO set es filter for isSalable()
        /*if(pluginApp(TemplateConfigService::class)->get('item.show_variation_over_dropdown') != 'true')
        {
            $searchFactory
        }*/
        
        if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
        {
            $searchFactory->hasItemId($options['itemId']);
        }
        
        return $searchFactory;
    }
}