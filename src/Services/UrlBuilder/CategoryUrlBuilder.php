<?php

namespace IO\Services\UrlBuilder;

use IO\Services\CategoryService;
use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Log\Loggable;

class CategoryUrlBuilder
{
    use Loggable;

    public function buildUrl( int $categoryId, string $lang = null, int $webstoreId = null): UrlQuery
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        /** @var CategoryService $categoryService */
        $categoryService = pluginApp( CategoryService::class );
        $category = $categoryService->get( $categoryId, $lang );

        if ( $category !== null )
        {
            if(is_null($webstoreId)){
                /** @var WebstoreConfigurationService $webstoreService */
                $webstoreService = pluginApp(WebstoreConfigurationService::class);
                $webstoreId = $webstoreService->getWebstoreConfig()->webstoreId;
            }

            return $this->buildUrlQuery(
                $categoryService->getURL( $category, $lang, $webstoreId ),
                $lang
            );
        }

        $this->getLogger('CategoryUrlBuilder')->error(
            'Cannot find category.',
            [
                'categoryId' => $categoryId,
                'lang'       => $lang
            ]
        );
        return $this->buildUrlQuery( '', $lang );
    }

    private function buildUrlQuery( $path, $lang ): UrlQuery
    {
        if(substr($path, 0, 4) === '/'.$lang.'/')
        {
            // FIX: category url already contains language, if it is different to default language
            $path = substr($path, 4);
        }
        return pluginApp(
            UrlQuery::class,
            ['path' => $path, 'lang' => $lang]
        );
    }
}
