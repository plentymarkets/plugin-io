<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

/**
 * Class RouteConfig
 *
 * Helper class for checking status of routes.
 *
 * @package IO\Helper
 */
class RouteConfig
{
    /*
     * Constants representing a single route.
     * Each route might be enabled/disabled by the plugin config.
     * Use this constants to check the type of the currently displayed page by using ShopUrls::is().
     */
    /** @var string Represents the basket route */
    const BASKET                    = "basket";
    /** @var string Represents the cancellation rights route */
    const CANCELLATION_RIGHTS       = "cancellation-rights";
    /** @var string Represents the cancellation form route */
    const CANCELLATION_FORM         = "cancellation-form";
    /** @var string Represents the category routes */
    const CATEGORY                  = "category";
    /** @var string Represents the change mail route */
    const CHANGE_MAIL               = "change-mail";
    /** @var string Represents the checkout route */
    const CHECKOUT                  = "checkout";
    /** @var string Represents the confirmation route */
    const CONFIRMATION              = "confirmation";
    /** @var string Represents the contact route */
    const CONTACT                   = "contact";
    /** @var string Represents the contact mail api route */
    const CONTACT_MAIL_API          = "contact-mail-api";
    /** @var string Represents the home route */
    const HOME                      = "home";
    /** @var string Represents the item routes */
    const ITEM                      = "item";
    /** @var string Represents the legal disclosure route */
    const LEGAL_DISCLOSURE          = "legal-disclosure";
    /** @var string Represents the login route */
    const LOGIN                     = "login";
    /** @var string Represents the my account route */
    const MY_ACCOUNT                = "my-account";
    /** @var string Represents the newsletter opt in route */
    const NEWSLETTER_OPT_IN         = "newsletter-opt-in";
    /** @var string Represents the newsletter opt out route */
    const NEWSLETTER_OPT_OUT        = "newsletter-opt-out";
    /** @var string Represents the order document route */
    const ORDER_DOCUMENT            = "order-document";
    /** @var string Represents the order property file route */
    const ORDER_PROPERTY_FILE       = "order-property-file";
    /** @var string Represents the order return route */
    const ORDER_RETURN              = "order-return";
    /** @var string Represents the order return confirmation route */
    const ORDER_RETURN_CONFIRMATION = "order-return-confirmation";
    /** @var string Represents the password reset route */
    const PASSWORD_RESET            = "password-reset";
    /** @var string Represents the place order route */
    const PLACE_ORDER               = "place-order";
    /** @var string Represents the privacy policy route */
    const PRIVACY_POLICY            = "privacy-policy";
    /** @var string Represents the register route */
    const REGISTER                  = "register";
    /** @var string Represents the search route */
    const SEARCH                    = "search";
    /** @var string Represents the terms and conditions route */
    const TERMS_CONDITIONS          = "gtc";
    /** @var string Represents the wish list route */
    const WISH_LIST                 = "wish-list";
    /** @var string Represents the 404 route */
    const PAGE_NOT_FOUND            = "page-not-found";
    /** @var string Represents the tags route */
    const TAGS                      = "tags";

    /** @var string[] Represents all routes */
    const ALL = [
        self::BASKET,
        self::CANCELLATION_RIGHTS,
        self::CANCELLATION_FORM,
        self::CATEGORY,
        self::CHANGE_MAIL,
        self::CHECKOUT,
        self::CONFIRMATION,
        self::CONTACT,
        self::CONTACT_MAIL_API,
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

    /** @var array $enabledRoutes Contains all active routes  */
    private static $enabledRoutes = null;
    /** @var array Contains overriden routes. Used for preview purposes in the shopBuilder */
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
     * If true, the default route should be registered in the route service provider.
     *
     * @param string $route The route to check the active state for.
     * @return bool
     */
    public static function isActive( $route )
    {
        self::getEnabledRoutes();
        return in_array($route, self::$enabledRoutes)
            && self::getCategoryId( $route ) === 0;
    }

    /**
     * Get the ID of the category linked to a specific route.
     * Returns 0 if no category is linked.
     *
     * @param string $route The route to get the linked category ID for.
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
     * Override the currently linked category ID for a specific route.
     * This is used by the shopBuilder to preview a category in a special context.
     *
     * @param string $route The route to override the linked category for.
     * @param int $categoryId The ID of the category to override.
     */
    public static function overrideCategoryId( $route, $categoryId )
    {
        self::$overrides[$route] = $categoryId;
    }

    /**
     * Check if blog routes should be recognized by the route service provider or not.
     * If true, it should pass requests to blog routes to the old CMS.
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
