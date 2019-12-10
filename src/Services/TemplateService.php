<?php //strict

namespace IO\Services;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Templates\Twig;

/**
 * Class TemplateService
 * @package IO\Services
 */
class TemplateService
{
    public static $currentTemplate = "";

    public static $currentTemplateData = [];
    
    public static $shouldBeCached = true;

    public $forceNoIndex = false;

    public function forceNoIndex($forceNoIndex)
    {
        $this->forceNoIndex = $forceNoIndex;
    }

    public function isNoIndexForced()
    {
        return $this->forceNoIndex;
    }
    
    public function shouldBeCached()
    {
        return self::$shouldBeCached;
    }
    
    public function disableCacheForTemplate()
    {
        self::$shouldBeCached = false;
    }

    public function getCurrentTemplate():string
    {
        return TemplateService::$currentTemplate;
    }

    public function setCurrentTemplate($template)
    {
        self::$currentTemplate = $template;
    }

    /**
     * @deprecated Use ShopUrls::is() instead
     * @param $templateToCheck
     * @return bool
     */
    public function isCurrentTemplate($templateToCheck):bool
    {
        return TemplateService::$currentTemplate == $templateToCheck;
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::HOME) instead
     */
    public function isHome():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::HOME);
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::ITEM) instead
     */
    public function isItem():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::ITEM);
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::MY_ACCOUNT) instead
     */
    public function isMyAccount():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::MY_ACCOUNT);
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::CHECKOUT) instead
     */
    public function isCheckout():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::CHECKOUT);
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::SEARCH) instead
     */
    public function isSearch():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::SEARCH);
    }

    /**
     * @deprecated Use ShopUrls::is(RouteConfig::CATEGORY) instead
     */
    public function isCategory():bool
    {
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        return $shopUrls->is(RouteConfig::CATEGORY);
    }
    
    public function renderTemplate($template, $params)
    {
        $renderedTemplate = '';
    
        if (strlen($template))
        {
            /**
             * @var Twig $twig
             */
            $twig             = pluginApp(Twig::class);
            $renderedTemplate = $twig->render($template, $params);
        }
        
        return $renderedTemplate;
    }
}
