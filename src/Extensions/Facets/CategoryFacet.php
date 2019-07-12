<?php
namespace IO\Extensions\Facets;
use IO\Services\CategoryService;
use IO\Services\ItemSearch\Contracts\FacetExtension;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Item\Search\Aggregations\CategoryAllTermsAggregation;
use Plenty\Modules\Item\Search\Aggregations\CategoryProcessor;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use IO\Helper\UserSession;
use Plenty\Plugin\Application;


class CategoryFacet implements FacetExtension
{
    // TODO: may be read from config?
    const MAX_RESULT_COUNT = 10;

    public function getAggregation(): AggregationInterface
    {
        /** @var CategoryProcessor $categoryProcessor */
        $categoryProcessor = pluginApp(CategoryProcessor::class);
        /** @var CategoryAllTermsAggregation $categoryAggregation */
        $categoryAggregation = pluginApp(CategoryAllTermsAggregation::class, [$categoryProcessor]);

        // FIX Set count to '-1' to get all categories. Categories will be filtered when merging results.
        $categoryAggregation->setSize(-1);

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
            if(count($result))
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
                    'maxResultCount' => self::MAX_RESULT_COUNT
                ];
                $loggedIn = pluginApp(UserSession::class)->isContactLoggedIn();

                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);

                foreach($result as $categoryId => $count)
                {
                    $category = $categoryService->getForPlentyId($categoryId, $sessionStorage->getLang());


                    if ( !is_null($category) && (!$categoryService->isHidden($category->id) || $loggedIn || pluginApp(Application::class)->isAdminPreview()) )
                    {
                        $categoryFacet['values'][] = [
                            'id' => 'category-' . $categoryId,
                            'name' => $category->details[0]->name,
                            'count' => $count,
                        ];

                        if ( count($categoryFacet['values']) === self::MAX_RESULT_COUNT )
                        {
                            break;
                        }
                    }

                }
            }


            if(count($categoryFacet['values']) > 0)
            {
                $categoryFacet['count'] = count($categoryFacet['values']);
            }
            else
            {
                $categoryFacet = [];
            }
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