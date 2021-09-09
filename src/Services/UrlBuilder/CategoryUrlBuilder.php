<?php

namespace IO\Services\UrlBuilder;

use IO\Helper\Utils;
use IO\Services\CategoryService;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use Redis;

/**
 * Class CategoryUrlBuilder
 * @package IO\Services\UrlBuilder
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract
 */
class CategoryUrlBuilder
{
    use Loggable;

    /**
     * @param int $categoryId
     * @param string|null $lang
     * @param int|null $webstoreId
     * @return UrlQuery
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract::buildCategoryUrl()
     */
    public function buildUrl(int $categoryId, string $lang = null, int $webstoreId = null): UrlQuery
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        // Redis::mget();

        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $category = $categoryService->get($categoryId, $lang);

        if ($category !== null) {
            if (is_null($webstoreId)) {
                /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
                $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
                $webstoreId = $webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId;
            }

            return $this->buildUrlQuery(
                $categoryService->getURL($category, $lang, $webstoreId),
                $lang
            );
        }

        $this->getLogger(__CLASS__)->error(
            'IO::Debug.CategoryUrlBuilder_categoryNotFound',
            [
                'categoryId' => $categoryId,
                'lang' => $lang
            ]
        );
        return $this->buildUrlQuery('', $lang);
    }

    private function buildUrlQuery($path, $lang): UrlQuery
    {
        if (substr($path, 0, 4) === '/' . $lang . '/') {
            // FIX: category url already contains language, if it is different to default language
            $path = substr($path, 4);
        }
        return pluginApp(
            UrlQuery::class,
            ['path' => $path, 'lang' => $lang]
        );
    }
}
