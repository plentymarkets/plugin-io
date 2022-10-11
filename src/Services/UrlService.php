<?php

namespace IO\Services;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\MemoryCache;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Service Class UrlService
 *
 * This service class contains functions related to url generation etc.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class UrlService
{
    use MemoryCache;

    /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
    private $webstoreConfigurationRepository;

    /**
     * UrlService constructor.
     * @param WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
     */
    public function __construct(
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
    )
    {
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
    }

    /**
     * Get canonical URL for a category
     * @param int $categoryId A category id to get the URL for
     * @param string|null $lang Optional: A language for the URL (ISO-639-1) (Default: The current language)
     * @param int|null $webstoreId Optional: A webstore id (Default: The current webstore id)
     * @return UrlQuery
     */
    public function getCategoryURL($categoryId, $lang = null, $webstoreId = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        if ($webstoreId === null) {
            $webstoreId = Utils::getWebstoreId();
        }
        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$categoryId.$lang.$webstoreId",
            function () use ($categoryId, $lang, $webstoreId) {
                /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
                $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);
                return $urlBuilderRepository->buildCategoryUrl($categoryId, $lang, $webstoreId);
            }
        );

        return $categoryUrl;
    }

    /**
     * Get canonical URL for a variation
     * @param int $itemId An item id to get URL for
     * @param int $variationId An variation id to get URL for
     * @param string|null $lang Optional: A language to get URL for (ISO-639-1) (Default: The current language)
     * @return UrlQuery
     */
    public function getVariationURL($itemId, $variationId, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $variationUrl = $this->fromMemoryCache(
            "variationUrl.$itemId.$variationId.$lang",
            function () use ($itemId, $variationId, $lang) {
                /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
                $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);
                $variationUrl = $urlBuilderRepository->buildVariationUrl($itemId, $variationId, $lang);

                if ($variationUrl->getPath() !== null) {
                    $variationUrl->append(
                        $urlBuilderRepository->getSuffix($itemId, $variationId)

                    );
                }

                return $variationUrl;
            }
        );

        return $variationUrl;
    }

    /**
     * Get canonical URL for current page
     * @param string|null $lang Optional: Language for the URL
     * @param bool $ignoreCanonical Optional: If true, get canonical from category details (Default: false)
     * @return string|null
     */
    public function getCanonicalURL($lang = null, $ignoreCanonical = false)
    {
        $defaultLanguage = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;

        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $canonicalUrl = $this->fromMemoryCache(
            "canonicalUrl.$lang.$ignoreCanonical",
            function () use ($lang, $defaultLanguage, $ignoreCanonical) {
                $includeLanguage = $lang !== null && $lang !== $defaultLanguage;
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                if (TemplateService::$currentTemplate === 'tpl.item') {
                    $currentItem = $categoryService->getCurrentItem();
                    if (is_array($currentItem) && count($currentItem) > 0) {
                        return $this
                            ->getVariationURL($currentItem['item']['id'], $currentItem['variation']['id'], $lang)
                            ->toAbsoluteUrl($includeLanguage);
                    }

                    return null;
                }

                if (substr(TemplateService::$currentTemplate, 0, 12) === 'tpl.category' ||
                    substr(TemplateService::$currentTemplate, 0, 12) === 'tpl.checkout' ||
                    substr(TemplateService::$currentTemplate, 0, 14) === 'tpl.my-account' ||
                    substr(TemplateService::$currentTemplate, 0, 11) === 'tpl.search') {

                    $currentCategory = $categoryService->getCurrentCategory();

                    if(RouteConfig::getCategoryId(RouteConfig::HOME) === $currentCategory->id) {
                        // FIX return homepage url as canonical when showing homepage category
                        return pluginApp(UrlQuery::class, ['path' => "", 'lang' => $lang])
                            ->toAbsoluteUrl($includeLanguage);
                    }

                    if ($currentCategory !== null) {
                        $categoryDetails = $categoryService->getDetails($currentCategory, $lang);

                        if ($categoryDetails !== null && strlen(
                                $categoryDetails->canonicalLink
                            ) > 0 && $ignoreCanonical === false) {
                            return $categoryDetails->canonicalLink;
                        }

                        return $this
                            ->getCategoryURL($currentCategory->id, $lang)
                            ->toAbsoluteUrl($includeLanguage);
                    } elseif (substr(TemplateService::$currentTemplate, 0, 11) === 'tpl.search') {
                        return pluginApp(UrlQuery::class, ['path' => RouteConfig::SEARCH, 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                    }

                    return null;
                } elseif (TemplateService::$currentTemplate === 'tpl.home' || TemplateService::$currentTemplate === 'tpl.home.category')   {
                    return pluginApp(UrlQuery::class, ['path' => "", 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                } elseif (TemplateService::$currentTemplate === 'tpl.login') {
                    return pluginApp(UrlQuery::class, ['path' => "login", 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                } elseif (TemplateService::$currentTemplate === "tpl.tags") {
                    /** @var Request $request */
                    $request = pluginApp(Request::class);
                    $path = explode('?', $request->getRequestUri());
                    return pluginApp(UrlQuery::class, ['path' => $path[0], 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                }

                return null;
            }
        );

        return $canonicalUrl;
    }

    /**
     * Get query string from URI, return an empty string if its an canonical link from category details
     * @return  string
     */
    public function getCanonicalQueryString(): string
    {
        $lang = Utils::getLang();

        if (substr(TemplateService::$currentTemplate, 0, 12) === 'tpl.category') {
            /** @var CategoryService $categoryService */
            $categoryService = pluginApp(CategoryService::class);
            $currentCategory = $categoryService->getCurrentCategory();

            if ($currentCategory !== null) {
                $categoryDetails = $categoryService->getDetails($currentCategory, $lang);

                if ($categoryDetails !== null &&
                    strlen($categoryDetails->canonicalLink) > 0) {
                    return '';
                }
            }
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);
        $queryParameters = $request->all();
        $queryParameters = Utils::cleanUpExcludesContentCacheParams($queryParameters);

        $queryParameters = http_build_query($queryParameters);
        return strlen($queryParameters) > 0 ? '?' . $queryParameters : '';
    }

    /**
     * Check if the current URL is canonical
     * @param string|null $lang Optional: A language for the check (Default: The current language)
     * @return bool
     */
    public function isCanonical($lang = null)
    {
        $defaultLanguage = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;

        if ($lang === null) {
            $lang = Utils::getLang();
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);
        $requestUri = $request->getRequestUri();

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $requestUri]);
        $requestUrl = $urlQuery->toAbsoluteUrl($lang !== $defaultLanguage);
        $canonical = $this->getCanonicalURL($lang);

        return $requestUrl === $canonical;
    }

    /**
     * Get equivalent canonical URLs for each active language
     * @return array
     */
    public function getLanguageURLs()
    {
        $languageUrls = $this->fromMemoryCache(
            "languageUrls",
            function () {
                $result = [];

                $defaultLanguage = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;

                $defaultUrl = $this->getCanonicalURL($defaultLanguage);

                if ($defaultUrl !== null) {
                    $result["x-default"] = $defaultUrl;
                }

                foreach ($this->webstoreConfigurationRepository->getActiveLanguageList() as $language) {
                    $url = $this->getCanonicalURL($language);
                    if ($url !== null) {
                        /** @var LocalizationRepositoryContract $localizationRepository */
                        $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
                        $languageISO = $localizationRepository->getLanguageCode($language);
                        $result[$languageISO] = $url;
                    }
                }

                return $result;
            }
        );

        return $languageUrls;
    }


    /**
     * Get language specific homepage URL
     * @return string
     * @deprecated since 4.3.0
     * Use IO\Extensions\Constants\ShopUrls::$home instead.
     */
    public function getHomepageURL()
    {
        /** @var ShopUrls $shopUrrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->home;
    }

    /**
     * Redirects to the given URL
     * @param string $redirectURL
     * @return mixed
     */
    public function redirectTo($redirectURL)
    {
        if (strpos($redirectURL, 'http:') !== 0 && strpos($redirectURL, 'https:') !== 0) {
            /** @var UrlQuery $query */
            $query = pluginApp(UrlQuery::class, ['path' => $redirectURL]);
            $redirectURL = $query->toAbsoluteUrl(
                $this->webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage !== Utils::getLang()
            );
        }

        /** @var Response $response */
        $response = pluginApp(Response::class);
        return $response->redirectTo($redirectURL, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Check if route is enabled or category is linked to route.
     * @param string $route
     * @return bool
     */
    public function isRouteEnabled($route)
    {
        return in_array($route, RouteConfig::getEnabledRoutes()) || RouteConfig::getCategoryId($route) > 0;
    }
}
