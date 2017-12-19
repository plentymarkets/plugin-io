<?php

namespace IO\Services;

use IO\Helper\ShopUrl;
use IO\Services\UrlBuilder\Category;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\UrlBuilder\Variation;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Plugin\Application;

class UrlService
{
    /**
     * Get canonical url for a category
     * @param int           $categoryId
     * @param string|null   $lang
     * @return UrlQuery
     */
    public function getCategoryURL( $categoryId, $lang = null )
    {
        /** @var Category $categoryUrlBuilder */
        $categoryUrlBuilder = pluginApp( Category::class );
        return $categoryUrlBuilder->buildUrl( $categoryId, $lang );
    }

    /**
     * Get canonical url for a variation
     * @param int           $itemId
     * @param int           $variationId
     * @param string|null   $lang
     * @return UrlQuery
     */
    public function getVariationURL( $itemId, $variationId, $lang = null )
    {
        /** @var Variation $variationUrlBuilder */
        $variationUrlBuilder = pluginApp( Variation::class );
        return $variationUrlBuilder
            ->buildUrl( $itemId, $variationId, $lang )
            ->append(
                $variationUrlBuilder->getSuffix( $itemId, $variationId )
            );
    }

    /**
     * Get canonical url for current page
     * @param string|null   $lang
     * @return UrlQuery|null
     */
    public function getCanonicalURL( $lang = null )
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp( CategoryService::class );
        if ( TemplateService::$currentTemplate === 'tpl.item' )
        {
            $currentItem = $categoryService->getCurrentItem();
            if ( count($currentItem) > 0 )
            {
                return $this
                    ->getVariationURL( $currentItem['item']['id'], $currentItem['variation']['id'], $lang );
            }

            return null;
        }

        if ( substr(TemplateService::$currentTemplate,0, 12) === 'tpl.category' )
        {
            $currentCategory = $categoryService->getCurrentCategory();

            if ( $currentCategory !== null )
            {
                return $this->getCategoryURL( $currentCategory->id, $lang );
            }
            return null;
        }

        if ( TemplateService::$currentTemplate === 'tpl.home' )
        {
            return pluginApp( UrlQuery::class, ['path' => "", 'lang' => $lang]);
        }

        return null;
    }

    /**
     * Get equivalent canonical urls for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $result = [];
        $defaultUrl = $this->getCanonicalURL();

        if ( $defaultUrl !== null )
        {
            $result["x-default"] = $defaultUrl->toAbsoluteUrl();
        }

        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp( WebstoreConfigurationService::class );
        foreach( $webstoreConfigService->getActiveLanguageList() as $language )
        {
            $url = $this->getCanonicalURL( $language );
            if ( $url !== null )
            {
                $result[$language] = $url->toAbsoluteUrl(true);
            }
        }

        return $result;
    }
}