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
    const ORDER_RETURN_CONFIRMATION = "order-return-confirmation";
    const NEWSLETTER_OPT_IN         = "newsletter-opt-in";
    const NEWSLETTER_OPT_OUT        = "newsletter-opt-out";

    private static $enabledRoutes = null;

    private static function getEnabledRoutes()
    {
        if ( is_null(self::$enabledRoutes) )
        {
            $config = pluginApp(ConfigRepository::class);
            self::$enabledRoutes = explode(", ",  $config->get("IO.routing.enabled_routes") );
        }

        return self::$enabledRoutes;
    }

    public static function isActive( $route )
    {
        self::getEnabledRoutes();
        return in_array($route, self::$enabledRoutes) || in_array("all", self::$enabledRoutes);
    }
}
