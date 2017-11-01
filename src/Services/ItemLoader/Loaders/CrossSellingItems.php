<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Constants\CrossSellingType;
use IO\Services\SessionStorageService;
use IO\Services\ItemCrossSellingService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\CrossSellingFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Plugin\Application;

/**
 * Class CrossSellingItems
 * @package IO\Services\ItemLoader\Loaders
 */
class CrossSellingItems implements ItemLoaderContract
{
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [pluginApp(SessionStorageService::class)->getLang()]]);
        
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        
        return pluginApp(DocumentSearch::class, [$documentProcessor]);
    }
    
    /**
     * @return array
     */
    public function getAggregations()
    {
        return [];
    }
    
    /**
     * @param array $options
     *
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        /** @var ClientFilter $clientFilter */
        $clientFilter = pluginApp(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());
        
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();
        $variationFilter->isMain();
    
        /**
         * @var ItemCrossSellingService $crossSellingService
         */
        $crossSellingService = pluginApp(ItemCrossSellingService::class);
        
        /**
         * @var CrossSellingFilter $crossSellingFilter
         */
        $crossSellingFilter = pluginApp(CrossSellingFilter::class, [$options['crossSellingItemId']]);
        
        if(isset($options['relation']) && strlen($options['relation']))
        {
            $crossSellingType = $options['relation'];
        }
        else
        {
            $crossSellingType = $crossSellingService->getType();
        }
        
        if($crossSellingType == 'dynamic')
        {
            $crossSellingFilter->isDynamic();
        }
        else
        {
            $crossSellingFilter->hasRelation($crossSellingType);
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
        
        return [
            $clientFilter,
            $variationFilter,
            $crossSellingFilter,
            $textFilter
        ];
    }
    
    /**
     * @param array $options
     * @return int
     */
    public function getCurrentPage($options = [])
    {
        return ( (INT)$options['page'] > 0 ? (INT)$options['page'] : 1 );
    }
    
    /**
     * @param array $options
     * @return int
     */
    public function getItemsPerPage($options = [])
    {
        return ( (INT)$options['items'] > 0 ? (INT)$options['items'] : 20 );
    }
}