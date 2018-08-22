<?php
namespace IO\Extensions\Facets;
use IO\Services\CategoryService;
use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Item\Search\Aggregations\CategoryAllTermsAggregation;
use Plenty\Modules\Item\Search\Aggregations\CategoryProcessor;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
class CategoryFacet implements FacetExtension
{
    public function getAggregation(): AggregationInterface
    {
        /** @var CategoryProcessor $categoryProcessor */
        $categoryProcessor = pluginApp(CategoryProcessor::class);
        /** @var CategoryAllTermsAggregation $categoryAggregation */
        $categoryAggregation = pluginApp(CategoryAllTermsAggregation::class, [$categoryProcessor]);
        
        return $categoryAggregation;
    }
    
    public function mergeIntoFacetsList($result): array
    {
        $categoryFacet = [];
        
        /** @var TemplateService $templateService */
        $templateService = pluginApp(TemplateService::class);
        
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        
        if(!$templateService->isCurrentTemplate('tpl.category.item') && $templateConfigService->get('item.show_category_filter') == 'true')
        {
            /** @var SessionStorageService $sessionStorage */
            $sessionStorage = pluginApp(SessionStorageService::class);
            
            $categoryFacet = [
                'id' => 'category',
                'name' => 'Categories',
                'translationKey' => 'itemFilterCategory',
                'position' => 0,
                'values' => [],
                'minHitCount' => 1,
                'maxResultCount' => 10
            ];
            
            if(count($result))
            {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                
                foreach($result as $categoryId => $count)
                {
                    $category = $categoryService->get($categoryId, $sessionStorage->getLang());
                    
                    $categoryFacet['values'][] = [
                        'id' => 'category-' . $categoryId,
                        'name' => $category->details[0]->name,
                        'count' => $count,
                    ];
                }
            }
            
            $categoryFacet['count'] = count($categoryFacet['values']);
        }
        
        return $categoryFacet;
    }
    
    public function extractFilterParams($filtersList)
    {
        $categoryIds = [];
        
        if(count($filtersList))
        {
            foreach ($filtersList as $filter)
            {
                if(strpos($filter, 'category-') === 0)
                {
                    $e = explode('-', $filter);
                    $categoryIds[] = $e[1];
                }
            }
            
            if(count($categoryIds))
            {
                /** @var CategoryFilter $categoryFilter */
                $categoryFilter = pluginApp(CategoryFilter::class);
                $categoryFilter->isInAtLeastOneCategory($categoryIds);
                
                return $categoryFilter;
            }
        }
        
        return null;
    }
}