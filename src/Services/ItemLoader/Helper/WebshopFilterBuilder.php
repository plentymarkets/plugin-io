<?php

namespace IO\Services\ItemLoader\Helper;

use IO\Services\PriceDetectService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

class WebshopFilterBuilder implements FilterBuilder
{
    public function getFilters($options):array
    {
        /** @var ClientFilter $clientFilter */
        $clientFilter = pluginApp(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());
    
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();
        $variationFilter->isHiddenInCategoryList(false);
    
        if(isset($options['useVariationShowType']) && $options['useVariationShowType'])
        {
            $variationShowType = $this->getVariationShowType($options);
            if(strlen($variationShowType) && $variationShowType !== 'combined')
            {
                if($variationShowType == 'main')
                {
                    $variationFilter->isMain();
                }
                elseif($variationShowType == 'child')
                {
                    $variationFilter->isChild();
                }
            }
        }
    
        $sessionLang = pluginApp(SessionStorageService::class)->getLang();
    
        $langMap = [
            'de' => TextFilter::LANG_DE,
            'fr' => TextFilter::LANG_FR,
            'en' => TextFilter::LANG_EN,
        ];
    
        /**
         * @var TextFilter $textFilter
         */
        $textFilter = pluginApp(TextFilter::class);
    
        if(isset($langMap[$sessionLang]))
        {
            $textFilterLanguage = $langMap[$sessionLang];
        
            /**
             * @var TemplateConfigService $templateConfigService
             */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $usedItemName = $templateConfigService->get('item.name');
        
            $textFilterType = TextFilter::FILTER_ANY_NAME;
            if(strlen($usedItemName))
            {
                if($usedItemName == '0')
                {
                    $textFilterType = TextFilter::FILTER_NAME_1;
                }
                elseif($usedItemName == '1')
                {
                    $textFilterType = TextFilter::FILTER_NAME_2;
                }
                elseif($usedItemName == '2')
                {
                    $textFilterType = TextFilter::FILTER_NAME_3;
                }
            }
        
            $textFilter->hasNameInLanguage($textFilterLanguage, $textFilterType);
        }
    
        /**
         * @var PriceDetectService $priceDetectService
         */
        $priceDetectService = pluginApp(PriceDetectService::class);
        $priceIds = $priceDetectService->getPriceIdsForCustomer();
    
        /**
         * @var SalesPriceFilter $priceFilter
         */
        $priceFilter = pluginApp(SalesPriceFilter::class);
        $priceFilter->hasAtLeastOnePrice($priceIds);
    
        return [
            $clientFilter,
            $variationFilter,
            $textFilter,
            $priceFilter,
        ];
    }
    
    public function getCollapseForCombinedVariations($options)
    {
        $variationShowType = $this->getVariationShowType($options);
        if(strlen($variationShowType) && $variationShowType == 'combined')
        {
            return pluginApp(BaseCollapse::class, ['ids.itemId']);
        }
        
        return null;
    }
    
    private function getVariationShowType($options)
    {
        $variationShowType = '';
        if(isset($options['variationShowType']) && strlen($options['variationShowType']))
        {
            $variationShowType = $options['variationShowType'];
        }
        else
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $variationShowType = $templateConfigService->get('item.variation_show_type', '');
        }
        
        return $variationShowType;
    }
}