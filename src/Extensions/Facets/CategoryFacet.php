<?php

namespace IO\Extensions\Facets;

use IO\Helper\Utils;
use IO\Services\CategoryService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Pim\SearchService\Aggregations\CategoryAllTermsAggregation;
use Plenty\Modules\Pim\SearchService\Aggregations\Processors\CategoryAllTermsAggregationProcessor;
use Plenty\Modules\Pim\SearchService\Filter\CategoryFilter;
use Plenty\Modules\Webshop\ItemSearch\Contracts\FacetExtension;
use Plenty\Plugin\Http\Request;

class CategoryFacet implements FacetExtension
{
    // TODO: may be read from config?
    const MAX_RESULT_COUNT = 10;

    public function getAggregation(): AggregationInterface
    {
        /** @var CategoryAllTermsAggregationProcessor $categoryProcessor */
        $categoryProcessor = pluginApp(CategoryAllTermsAggregationProcessor::class);
        /** @var CategoryAllTermsAggregation $categoryAggregation */
        $categoryAggregation = pluginApp(CategoryAllTermsAggregation::class, [$categoryProcessor]);

        return $categoryAggregation;
    }

    /**
     * @param array $result
     * @return array
     */
    public function mergeIntoFacetsList($result): array
    {
        $categoryFacet = [];

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        if ($templateConfigService->getBoolean('item.show_category_filter', false)) {
            if (is_array($result) && count($result)) {
                $categoryFacet = [
                    'id' => 'category',
                    'name' => 'Categories',
                    'translationKey' => 'itemFilterCategory',
                    'position' => 0,
                    'values' => [],
                    'minHitCount' => 1,
                    'maxResultCount' => self::MAX_RESULT_COUNT,
                    'type' => 'category'
                ];

                $loggedIn = Utils::isContactLoggedIn();

                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);

                $currentCategory = $categoryService->getCurrentCategory();

                $categoryBranch = null;
                if (!is_null($currentCategory)) {
                    $categoryBranch = $currentCategory->branch()->get()[0];
                    $categoryBranch = array_unique(array_values($categoryBranch->toArray()));
                }
                /** @var Request $request */
                $request = pluginApp(Request::class);
                foreach ($result as $categoryId => $count) {
                    $category = $categoryService->getForPlentyId($categoryId, Utils::getLang());
                    if (!is_null($category) && (is_null($categoryBranch) || !in_array(
                                $categoryId,
                                $categoryBranch ?? []
                            )) && (!$categoryService->isHidden(
                                $category->id
                            ) || $loggedIn || Utils::isAdminPreview())) {
                        $categoryFacet['values'][] = [
                            'id' => 'category-' . $categoryId,
                            'name' => $category->details[0]->name,
                            'count' => $count,
                            'selected' => strpos($request->getQueryString(),'category-' . $categoryId)
                        ];

                        if (count($categoryFacet['values']) === self::MAX_RESULT_COUNT) {
                            break;
                        }
                    }
                }
            }

            if (!is_null($currentCategory)) {
                $categoryService->setCurrentCategoryID($currentCategory->id);
            }

            if (is_array($categoryFacet['values']) && count($categoryFacet['values']) > 0) {
                $categoryFacet['count'] = count($categoryFacet['values']);
            } else {
                $categoryFacet = [];
            }
        }

        return $categoryFacet;
    }

    /**
     * @param array $filtersList
     * @return mixed|CategoryFilter|null
     */
    public function extractFilterParams($filtersList)
    {
        $categoryIds = [];

        if (is_array($filtersList) && count($filtersList)) {
            foreach ($filtersList as $filter) {
                if (strpos($filter, 'category-') === 0) {
                    $e = explode('-', $filter);
                    $categoryIds[] = $e[1];
                }
            }

            if (count($categoryIds)) {
                /** @var CategoryFilter $categoryFilter */
                $categoryFilter = pluginApp(CategoryFilter::class);
                $categoryFilter->isInAtLeastOneCategory($categoryIds);

                return $categoryFilter;
            }
        }

        return null;
    }
}
