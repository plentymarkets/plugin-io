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
    ) {
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
    }

    /**
     * Get canonical url for a category
     * @param int $categoryId
     * @param string|null $lang
     * @return UrlQuery
     */
    public function getCategoryURL($categoryId, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }
        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$categoryId.$lang",
            function () use ($categoryId, $lang) {
                /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
                $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

                return $urlBuilderRepository->buildCategoryUrl($categoryId, $lang);
            }
        );

        return $categoryUrl;
    }

    /**
     * Get canonical url for a variation
     * @param int $itemId
     * @param int $variationId
     * @param string|null $lang
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
     * Get canonical url for current page
     * @param string|null $lang
     * @param bool $ignoreCanonical
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
                    if (count($currentItem) > 0) {
                        return $this
                            ->getVariationURL($currentItem['item']['id'], $currentItem['variation']['id'], $lang)
                            ->toAbsoluteUrl($includeLanguage);
                    }

                    return null;
                }

                if (substr(TemplateService::$currentTemplate, 0, 12) === 'tpl.category') {
                    $currentCategory = $categoryService->getCurrentCategory();

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
                    }
                    return null;
                }

                if (TemplateService::$currentTemplate === 'tpl.home' || TemplateService::$currentTemplate === 'tpl.home.category') {
                    return pluginApp(UrlQuery::class, ['path' => "", 'lang' => $lang])
                        ->toAbsoluteUrl($includeLanguage);
                }

                return null;
            }
        );

        return $canonicalUrl;
    }

    /**
     * Check if the current URL is canonical
     * @param null $lang
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
     * Get equivalent canonical urls for each active language
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
     * Get language specific homepage url
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
     * @param $redirectURL
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
        return $response->redirectTo($redirectURL);
    }

    /**
     * Check if route is enabled or category is linked to route.
     * @param $route
     * @return bool
     */
    public function isRouteEnabled($route)
    {
        return in_array($route, RouteConfig::getEnabledRoutes()) || RouteConfig::getCategoryId($route) > 0;
    }
}
