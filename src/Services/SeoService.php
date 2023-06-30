<?php

namespace IO\Services;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use Plenty\Plugin\Http\Request;

/**
 * Service Class SeoService
 *
 * This service class contains functions related to search engine optimization.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class SeoService
{
    /**
     * Get meta robot information
     *
     * Get meta robot information, with a given value for error case (defined rules in method) $returnValueErrorCase
     * and a default value when category data is not set $defaultValueWithoutCategoryData.
     * $defaultValueWithoutCategoryData can be changed after rule check
     *
     * @param string $returnValueErrorCase Optional: Value for error case (Default: 'NOINDEX')
     * @param string $defaultValueWithoutCategoryData Optional: Default value when category data is not set (Default: 'ALL')
     * @return string
     */
    public function getRobotsInformation($returnValueErrorCase = 'NOINDEX', $defaultValueWithoutCategoryData = 'ALL'): string
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $currentCategory = $categoryService->getCurrentCategory();

        if (is_null($currentCategory)) {
            $robots = $defaultValueWithoutCategoryData;
        } else {
            $robots = str_replace('_', ', ', $currentCategory->details[0]->metaRobots);
            if (strlen($currentCategory->details[0]->canonicalLink) !== 0) {
                return $robots;
            }
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);
        $queryParameters = $request->all();
        $queryParameters = Utils::cleanUpExcludesContentCacheParams($queryParameters);

        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        if ($shopUrls->is(RouteConfig::SEARCH)) {
            if (count($queryParameters) === 1 && isset($queryParameters['query'])) {
                return $robots;
            } elseif (count($queryParameters) === 2 && isset($queryParameters['query']) && $queryParameters['page']) {
                /** @var TemplateConfigService $templateConfig */
                $templateConfig = pluginApp(TemplateConfigService::class);
                $pageNumberMarkAsNoIndex = $templateConfig->getInteger('pagination.noIndex');
                $currentPage = (int)$queryParameters['page'];
                if ($pageNumberMarkAsNoIndex == 0 || $currentPage < $pageNumberMarkAsNoIndex) {
                    return $robots;
                }
            }
        } elseif (count($queryParameters) === 0) {
            return $robots;
        } elseif (count($queryParameters) === 1 && isset($queryParameters['page'])) {
            /** @var TemplateConfigService $templateConfig */
            $templateConfig = pluginApp(TemplateConfigService::class);
            $pageNumberMarkAsNoIndex = $templateConfig->getInteger('pagination.noIndex');
            $currentPage = (int)$queryParameters['page'];
            if ($pageNumberMarkAsNoIndex == 0 || $currentPage < $pageNumberMarkAsNoIndex) {
                return $robots;
            }
        }

        return $returnValueErrorCase;
    }
}
