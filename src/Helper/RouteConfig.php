<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

class RouteConfig
{
    /*
     * Constants representing a single route.
     * Each route might be enabled/disabled by the plugin config.
     * Use this constants to check the type of the currently displayed page by using ShopUrls::is().
     */
    const BASKET                    = "basket";
    const CANCELLATION_RIGHTS       = "cancellation-rights";
    const CANCELLATION_FORM         = "cancellation-form";
    const CATEGORY                  = "category";
    const CHANGE_MAIL               = "change-mail";
    const CHECKOUT                  = "checkout";
    const CONFIRMATION              = "confirmation";
    const CONTACT                   = "contact";
    const HOME                      = "home";
    const ITEM                      = "item";
    const LEGAL_DISCLOSURE          = "legal-disclosure";
    const LOGIN                     = "login";
    const MY_ACCOUNT                = "my-account";
    const NEWSLETTER_OPT_IN         = "newsletter-opt-in";
    const NEWSLETTER_OPT_OUT        = "newsletter-opt-out";
    const ORDER_DOCUMENT            = "order-document";
    const ORDER_PROPERTY_FILE       = "order-property-file";
    const ORDER_RETURN              = "order-return";
    const ORDER_RETURN_CONFIRMATION = "order-return-confirmation";
    const PASSWORD_RESET            = "password-reset";
    const PLACE_ORDER               = "place-order";
    const PRIVACY_POLICY            = "privacy-policy";
    const REGISTER                  = "register";
    const SEARCH                    = "search";
    const TERMS_CONDITIONS          = "gtc";
    const WISH_LIST                 = "wish-list";
    const PAGE_NOT_FOUND            = "page-not-found";
    const TAGS                      = "tags";

    const ALL = [
        self::BASKET,
        self::CANCELLATION_RIGHTS,
        self::CANCELLATION_FORM,
        self::CATEGORY,
        self::CHANGE_MAIL,
        self::CHECKOUT,
        self::CONFIRMATION,
        self::CONTACT,
        self::HOME,
        self::ITEM,
        self::LEGAL_DISCLOSURE,
        self::LOGIN,
        self::MY_ACCOUNT,
        self::NEWSLETTER_OPT_IN,
        self::NEWSLETTER_OPT_OUT,
        self::ORDER_DOCUMENT,
        self::ORDER_PROPERTY_FILE,
        self::ORDER_RETURN,
        self::ORDER_RETURN_CONFIRMATION,
        self::PASSWORD_RESET,
        self::PLACE_ORDER,
        self::PRIVACY_POLICY,
        self::REGISTER,
        self::SEARCH,
        self::TAGS,
        self::TERMS_CONDITIONS,
        self::WISH_LIST,
        self::PAGE_NOT_FOUND
    ];

    private static $enabledRoutes = null;
    private static $overrides = [];

    /**
     * Get all enabled routes from the plugin config.
     * @return array
     */
    public static function getEnabledRoutes()
    {
        if ( is_null(self::$enabledRoutes) )
        {
            $config = pluginApp(ConfigRepository::class);
            $configValue = $config->get("IO.routing.enabled_routes");

            if ( $configValue === "all" || Utils::isShopBuilder() )
            {
                self::$enabledRoutes = self::ALL;
            }
            else
            {
                self::$enabledRoutes = explode(", ",  $configValue );
            }

        }

        return self::$enabledRoutes;
    }

    /**
     * Check if a route is enabled and no category is linked for this page.
     * If true the default route should be registered in the route service provider.
     *
     * @param string $route The route to check active state for.
     * @return bool
     */
    public static function isActive( $route )
    {
        self::getEnabledRoutes();
        return in_array($route, self::$enabledRoutes)
            && self::getCategoryId( $route ) === 0;
    }

    /**
     * Get the id of the category linked to a specific route.
     * Returns 0 if no category is linked.
     *
     * @param string $route The route to get the linked category id for.
     * @return int
     */
    public static function getCategoryId( $route )
    {
        if ( array_key_exists( $route, self::$overrides ) )
        {
            return self::$overrides[$route];
        }
        $config = pluginApp(ConfigRepository::class);
        return (int) $config->get('IO.routing.category_' . $route, 0);
    }

    /**
     * Override the currently linked category id for a specific route.
     * This is used by the shopbuilder to preview a category in a special context.
     *
     * @param string $route The route to override the linked category for.
     * @param int $categoryId The id of the category to override.
     */
    public static function overrideCategoryId( $route, $categoryId )
    {
        self::$overrides[$route] = $categoryId;
    }

    /**
     * Check if blog routes should be recognized by the route service provider or not.
     * If true it should pass requests to blog routes to the old CMS.
     *
     * @return bool
     */
    public static function passThroughBlogRoutes()
    {
        $config = pluginApp(ConfigRepository::class);
        $value = $config->get('IO.routing.pass_through_blog');
        return $value === "true" || $value === "1" || $value === 1;
    }
}
