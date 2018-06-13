<?php

namespace IO\Services\UrlBuilder;

use IO\Services\CategoryService;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Log\Loggable;

class CategoryUrlBuilder
{
    use Loggable;

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
        return pluginApp(
            UrlQuery::class,
            ['path' => $path, 'lang' => $lang]
        );
    }
}