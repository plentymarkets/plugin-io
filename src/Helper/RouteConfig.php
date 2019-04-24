<?php

namespace IO\Helper;

use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\ConfigRepository;

class RouteConfig
{
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

    private static $enabledRoutes = null;
    private static $overrides = [];

    public static function getEnabledRoutes()
    {
        if ( is_null(self::$enabledRoutes) )
        {
            $config = pluginApp(ConfigRepository::class);
            $configValue = $config->get("IO.routing.enabled_routes");

            if ( $configValue === "all" || pluginApp(ShopBuilderRequest::class)->isShopBuilder() )
            {
                self::$enabledRoutes = [
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
                    self::ORDER_PROPERTY_FILE,
                    self::ORDER_RETURN,
                    self::ORDER_RETURN_CONFIRMATION,
                    self::PASSWORD_RESET,
                    self::PLACE_ORDER,
                    self::PRIVACY_POLICY,
                    self::REGISTER,
                    self::SEARCH,
                    self::TERMS_CONDITIONS,
                    self::WISH_LIST,
                ];
            }
            else
            {
                self::$enabledRoutes = explode(", ",  $configValue );
            }

        }

        return self::$enabledRoutes;
    }

    public static function isActive( $route )
    {
        self::getEnabledRoutes();
        return in_array($route, self::$enabledRoutes)
            && self::getCategoryId( $route ) === 0;
    }

    public static function getCategoryId( $route )
    {
        if ( array_key_exists( $route, self::$overrides ) )
        {
            return self::$overrides[$route];
        }
        $config = pluginApp(ConfigRepository::class);
        return (int) $config->get('IO.routing.category_' . $route, 0);
    }

    public static function overrideCategoryId( $route, $categoryId )
    {
        self::$overrides[$route] = $categoryId;
    }
}