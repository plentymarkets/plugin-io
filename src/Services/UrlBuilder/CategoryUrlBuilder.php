<?php

namespace IO\Services\UrlBuilder;

use IO\Services\CategoryService;
use IO\Services\SessionStorageService;

class CategoryUrlBuilder
{
    public function buildUrl( int $categoryId, string $lang = null ): UrlQuery
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
            return $this->buildUrlQuery(
                $categoryService->getURL( $category, $lang ),
                $lang
            );
        }
    }

    private function buildUrlQuery( $path, $lang ): UrlQuery
    {
        return pluginApp(
            UrlQuery::class,
            ['path' => $path, 'lang' => $lang]
        );
    }
}