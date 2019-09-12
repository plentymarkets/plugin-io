<?php

namespace IO\Services;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\LanguageMap;
use IO\Helper\MemoryCache;
use IO\Helper\Utils;
use IO\Services\UrlBuilder\CategoryUrlBuilder;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\UrlBuilder\VariationUrlBuilder;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;


class UrlService
{
    use MemoryCache;

    /**
     * @var SessionStorageService $sessionStorage
     */
    private $sessionStorage;

    /**
     * @var WebstoreConfigurationService $webstoreConfigurationService
     */
    private $webstoreConfigurationService;

    /**
     * UrlService constructor.
     */
    public function __construct()
    {
        $this->sessionStorage = pluginApp(SessionStorageService::class);
        $this->webstoreConfigurationService = pluginApp(WebstoreConfigurationService::class);
    }

    /**
     * Get canonical url for a category
     * @param int           $categoryId
     * @param string|null   $lang
     * @param int | null $webstoreId
     * @return UrlQuery
     */
    public function getCategoryURL( $categoryId, $lang = null)
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorage->getLang();
        }
        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$categoryId.$lang",
            function() use ($categoryId, $lang) {
                /** @var CategoryUrlBuilder $categoryUrlBuilder */
                $categoryUrlBuilder = pluginApp( CategoryUrlBuilder::class );
                return $categoryUrlBuilder->buildUrl( $categoryId, $lang );
            }
        );

        return $categoryUrl;
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
        if ( $lang === null )
        {
            $lang = $this->sessionStorage->getLang();
        }

        $variationUrl = $this->fromMemoryCache(
            "variationUrl.$itemId.$variationId.$lang",
            function() use ($itemId, $variationId, $lang) {
                /** @var VariationUrlBuilder $variationUrlBuilder */
                $variationUrlBuilder = pluginApp( VariationUrlBuilder::class );
                $variationUrl = $variationUrlBuilder->buildUrl( $itemId, $variationId, $lang );

                if ( $variationUrl->getPath() !== null )
                {
                    $variationUrl->append(
                        $variationUrlBuilder->getSuffix( $itemId, $variationId )
                    );
                }

                return $variationUrl;
            }
        );

        return $variationUrl;
    }

    /**
     * Get canonical url for current page
     * @param string|null   $lang
     * @param bool $ignoreCanonical
     * @return string|null
     */
    public function getCanonicalURL( $lang = null, $ignoreCanonical = false)
    {
        $defaultLanguage = $this->webstoreConfigurationService->getDefaultLanguage();

        if ( $lang === null )
        {
            $lang = $this->sessionStorage->getLang();
        }

        $canonicalUrl = $this->fromMemoryCache(
            "canonicalUrl.$lang.$ignoreCanonical",
            function() use ($lang, $defaultLanguage, $ignoreCanonical) {
                $includeLanguage = $lang !== null && $lang !== $defaultLanguage;
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp( CategoryService::class );
                if ( TemplateService::$currentTemplate === 'tpl.item' )
                {
                    $currentItem = $categoryService->getCurrentItem();
                    if ( count($currentItem) > 0 )
                    {
                        return $this
                            ->getVariationURL( $currentItem['item']['id'], $currentItem['variation']['id'], $lang )
                            ->toAbsoluteUrl($includeLanguage);
                    }

                    return null;
                }

                if ( substr(TemplateService::$currentTemplate,0, 12) === 'tpl.category' )
                {
                    $currentCategory = $categoryService->getCurrentCategory();

                    if ( $currentCategory !== null )
                    {
                        $categoryDetails = $categoryService->getDetails( $currentCategory, $lang );

                        if($categoryDetails !== null && strlen($categoryDetails->canonicalLink) > 0 && $ignoreCanonical === false)
                        {
                            return $categoryDetails->canonicalLink;
                        }

                        return $this
                            ->getCategoryURL( $currentCategory->id, $lang )
                            ->toAbsoluteUrl($includeLanguage);
                    }
                    return null;
                }

                if ( TemplateService::$currentTemplate === 'tpl.home' || TemplateService::$currentTemplate === 'tpl.home.category' )
                {
                    return pluginApp( UrlQuery::class, ['path' => "", 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                }

                return null;
            }
        );

        return $canonicalUrl;

    }

    public function isCanonical($lang = null)
    {
        $defaultLanguage = $this->webstoreConfigurationService->getDefaultLanguage();

        if($lang === null)
        {
            $lang = $this->sessionStorage->getLang();
        }

        /** @var Request $request */
        $request    = pluginApp(Request::class);
        $requestUri = $request->getRequestUri();

        /** @var UrlQuery $urlQuery */
        $urlQuery   = pluginApp( UrlQuery::class, ['path' => $requestUri]);
        $requestUrl = $urlQuery->toAbsoluteUrl($lang !== $defaultLanguage);
        $canonical = $this->getCanonicalURL($lang);

        return $requestUrl === $canonical;
    }

    /**
     * Get equivalent canonical urls for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $languageUrls = $this->fromMemoryCache(
            "languageUrls",
            function() {
                $result = [];

                $defaultLanguage = $this->webstoreConfigurationService->getDefaultLanguage();

                $defaultUrl = $this->getCanonicalURL( $defaultLanguage );

                if ( $defaultUrl !== null )
                {
                    $result["x-default"] = $defaultUrl;
                }

                foreach($this->webstoreConfigurationService->getActiveLanguageList() as $language )
                {
                    $url = $this->getCanonicalURL( $language );
                    if ( $url !== null )
                    {
                        $languageISO = LanguageMap::getLanguageCode($language);
                        $result[$languageISO] = $url;
                    }
                }

                return $result;
            }
        );

        return $languageUrls;
    }


    /**
     * Get language specific homepage url
     * @return string
     * @deprecated since 4.3.0
     * Use ShopUrls::$home instead.
     */
    public function getHomepageURL()
    {
        /** @var ShopUrls $shopUrrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->home;
    }

    public function redirectTo($redirectURL)
    {
        if(strpos($redirectURL, 'http:') !== 0 && strpos($redirectURL, 'https:') !== 0)
        {
            $redirectURL = Utils::makeRelativeUrl(
                $redirectURL,
                $this->webstoreConfigurationService->getDefaultLanguage() !== $this->sessionStorage->getLang()
            );
        }

        /** @var Response $response */
        $response = pluginApp(Response::class);
        return $response->redirectTo($redirectURL);
    }
}
