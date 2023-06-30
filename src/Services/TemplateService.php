<?php //strict

namespace IO\Services;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;

/**
 * Service Class TemplateService
 *
 * This service class contains functions related to templating functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class TemplateService
{
    /**
     * @var string $currentTemplate Stores the id of the current template
     */
    public static $currentTemplate = "";

    /**
     * @var array $currentTemplateData Stores data for the current template
     */
    public static $currentTemplateData = [];

    /**
     * @var bool $shouldBeCached If true, template will be cached in the content cache (Default: true)
     */
    public static $shouldBeCached = true;

    /**
     * @var bool If true, force the NOINDEX robots attribute for this template
     */
    public $forceNoIndex = false;

    /**
     * Setter for the $forceNoIndex property.
     * @param bool $forceNoIndex If true, force the NOINDEX robots attribute for this template.
     */
    public function forceNoIndex($forceNoIndex)
    {
        $this->forceNoIndex = $forceNoIndex;
    }

    /**
     * Getter for the $forceNoIndex property. If true, force the NOINDEX robots attribute for this template.
     * @return bool
     */
    public function isNoIndexForced()
    {
        return $this->forceNoIndex;
    }

    /**
     * Getter for the $shouldBeCached property. If true, template will be cached in the content cache
     * @return bool
     */
    public function shouldBeCached()
    {
        return self::$shouldBeCached;
    }

    /**
     * Disable the content caching for this template
     */
    public function disableCacheForTemplate()
    {
        self::$shouldBeCached = false;
    }

    /**
     * Getter for the $currentTemplate property. Returns the id of the current template.
     * @return string
     */
    public function getCurrentTemplate(): string
    {
        return TemplateService::$currentTemplate;
    }

    /**
     * Setter for the $currentTemplate property.
     * @param string $template Identifier of a template
     */
    public function setCurrentTemplate($template)
    {
        self::$currentTemplate = $template;
    }

    /**
     * Check if the current template is same as the parameter
     * @param string $templateToCheck A template id to compare against
     * @return bool
     * @deprecated Use ShopUrls::is() instead
     */
    public function isCurrentTemplate($templateToCheck): bool
    {
        return TemplateService::$currentTemplate == $templateToCheck;
    }

    /**
     * Check if the current template is the home template
     * @deprecated Use ShopUrls::is(RouteConfig::HOME) instead
     */
    public function isHome(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::HOME);
    }

    /**
     * Check if the current template is the item template
     * @deprecated Use ShopUrls::is(RouteConfig::ITEM) instead
     */
    public function isItem(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::ITEM);
    }

    /**
     * Check if the current template is the my account template
     * @deprecated Use ShopUrls::is(RouteConfig::MY_ACCOUNT) instead
     */
    public function isMyAccount(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::MY_ACCOUNT);
    }

    /**
     * Check if the current template is the checkout template
     * @deprecated Use ShopUrls::is(RouteConfig::CHECKOUT) instead
     */
    public function isCheckout(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::CHECKOUT);
    }

    /**
     * Check if the current template is the search template
     * @deprecated Use ShopUrls::is(RouteConfig::SEARCH) instead
     */
    public function isSearch(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::SEARCH) || $shopUrls->is(RouteConfig::TAGS);
    }

    /**
     * Check if the current template is the category template
     * @deprecated Use ShopUrls::is(RouteConfig::CATEGORY) instead
     */
    public function isCategory(): bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::CATEGORY);
    }

    /**
     * Render a twig template into a string
     * @param string $template A twig template to render
     * @param array $params Environmental data for the twig template
     * @return string The rendered template
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderTemplate($template, $params)
    {
        $renderedTemplate = '';

        if (strlen($template)) {
            /**
             * @var Twig $twig
             */
            $twig = pluginApp(Twig::class);
            $renderedTemplate = $twig->render($template, $params);
        }

        return $renderedTemplate;
    }

    /**
     * Check if the price sorting returns the cheapest price
     * @return bool
     */
    public function isCheapestSorting()
    {
        /** @var TemplateConfigService $templateConfigRepository */
        $templateConfigRepository = pluginApp(TemplateConfigService::class);

        $sorting = pluginApp(Request::class)->get('sorting', '');
        if (is_array($sorting)) {
            // only one sorting value is accepted
            // reset sorting value
            $sorting = '';
        }

        if (strlen($sorting) === 0) {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
            if ($shopUrls->is(RouteConfig::SEARCH)) {
                $sorting = $templateConfigRepository->get('sort.defaultSortingSearch', 'item.score');
            } else {
                $sorting = $templateConfigRepository->get('sort.defaultSorting', 'texts.name1_asc');
            }
        }

        /** @var SortingHelper $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);
        $sorting = $sortingHelper->mapToInnerSorting($sorting);

        $dynamicInheritSorting = $templateConfigRepository->get('sorting.dynamicInherit', []);
        if (is_string($dynamicInheritSorting)) {
            $dynamicInheritSorting = explode(',' , $dynamicInheritSorting);
        }

        if (is_array($dynamicInheritSorting) && in_array($sorting, $dynamicInheritSorting)) {
            if ($sorting === 'filter.prices.price_asc') {
                return true;
            }
            return false;
        }

        $dynamicPrio1 = $templateConfigRepository->get('sorting.dynamicPrio1', 'filter.prices.price_asc');
        if ($dynamicPrio1 === 'filter.prices.price_asc') {
            return true;
        }

        $dynamicPrio2 = $templateConfigRepository->get('sorting.dynamicPrio2', 'variationId_asc');
        if ($dynamicPrio1 === 'filter.isMain_desc' && $dynamicPrio2 === 'filter.prices.price_asc') {
            return true;
        }

        return false;
    }
}
