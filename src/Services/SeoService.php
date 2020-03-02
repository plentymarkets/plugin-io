<?php

namespace IO\Services;

use Plenty\Plugin\Http\Request;

/**
 * Class SeoService
 *
 * @package IO\Services
 */
class SeoService
{
    public function __construct()
    {
    }

    public function getRobotsInformation()
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $currentCategory = $categoryService->getCurrentCategory();
        $robots = str_replace('_', ', ', $currentCategory->details[0]->metaRobots);

        if (strlen($currentCategory->details[0]->canonicalLink) !== 0) {
            return $robots;
        }


        $request = pluginApp(Request::class);
        $queryParameters = $request->all();
        unset($queryParameters['plentyMarkets']);

        if (TemplateService::$currentTemplate === 'tpl.search') {
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


        return 'NOINDEX';
    }
}
