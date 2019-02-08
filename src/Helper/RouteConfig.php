<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

class RouteConfig
{
    const HOME                      = "home";
    const BASKET                    = "basket";
    const CHECKOUT                  = "checkout";
    const MY_ACCOUNT                = "my-account";
    const CONFIRMATION              = "confirmation";
    const LOGIN                     = "login";
    const REGISTER                  = "register";
    const PASSWORD_RESET            = "password-reset";
    const SEARCH                    = "search";
    const PLACE_ORDER               = "place-order";
    const CANCELLATION_RIGHTS       = "cancellation-rights";
    const CANCELLATION_FORM         = "cancellation-form";
    const LEGAL_DISCLOSURE          = "legal-disclosure";
    const PRIVACY_POLICY            = "privacy-policy";
    const TERMS_CONDITIONS          = "gtc";
    const CONTACT                   = "contact";
    const ITEM                      = "item";
    const CATEGORY                  = "category";
    const WISH_LIST                 = "wish-list";
    const ORDER_RETURN              = "order-return";
    const ORDER_PROPERTY_FILE       = "order-property-file";
    const ORDER_DOCUMENT            = "order-document";
    const ORDER_RETURN_CONFIRMATION = "order-return-confirmation";
    const NEWSLETTER_OPT_IN         = "newsletter-opt-in";
    const NEWSLETTER_OPT_OUT        = "newsletter-opt-out";

    private static $enabledRoutes = null;

    public static function getEnabledRoutes()
    {
        if ( is_null(self::$enabledRoutes) )
        {
            $config = pluginApp(ConfigRepository::class);
            $configValue = $config->get("IO.routing.enabled_routes");
            if ( $configValue === "all" )
            {
                self::$enabledRoutes = [
                    self::HOME,
                    self::BASKET,
                    self::CHECKOUT,
                    self::MY_ACCOUNT,
                    self::CONFIRMATION,
                    self::LOGIN,
                    self::REGISTER,
                    self::PASSWORD_RESET,
                    self::SEARCH,
                    self::PLACE_ORDER,
                    self::CANCELLATION_RIGHTS,
                    self::CANCELLATION_FORM,
                    self::LEGAL_DISCLOSURE,
                    self::PRIVACY_POLICY,
                    self::TERMS_CONDITIONS,
                    self::CONTACT,
                    self::ITEM,
                    self::CATEGORY,
                    self::WISH_LIST,
                    self::ORDER_RETURN,
                    self::ORDER_PROPERTY_FILE,
                    self::ORDER_RETURN_CONFIRMATION,
                    self::NEWSLETTER_OPT_IN,
                    self::NEWSLETTER_OPT_OUT
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
        $config = pluginApp(ConfigRepository::class);
        return (int) $config->get('IO.routing.category_' . $route, 0);
    }
}