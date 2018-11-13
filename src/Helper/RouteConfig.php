<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

class RouteConfig
{
    const BASKET                    = "basket";
    const CHECKOUT                  = "checkout";
    const MY_ACCOUNT                = "my-account";
    const CONFIRMATION              = "confirmation";
    const LOGIN                     = "login";
    const REGISTER                  = "register";
    const PLACE_ORDER               = "place-order";
    const SEARCH                    = "search";
    const HOME                      = "home";
    const CANCELLATION_RIGHTS       = "cancellation-rights";
    const CANCELLATION_FORM         = "cancellation-form";
    const LEGAL_DISCLOSURE          = "legal-disclosure";
    const PRIVACY_POLICY            = "privacy-policy";
    const TERMS_CONDITIONS          = "gtc";
    const WISH_LIST                 = "wish-list";
    const ORDER_RETURN              = "order-return";
    const ORDER_RETURN_CONFIRMATION = "order-return-confirmation";
    const CONTACT                   = "contact";
    const PASSWORD_RESET            = "password-reset";
    const ORDER_PROPERTY_FILE       = "order-property-file";
    const NEWSLETTER_OPT_IN         = "newsletter-opt-in";
    const NEWSLETTER_OPT_OUT        = "newsletter-opt-out";
    const ITEM                      = "item";
    const CATEGORY                  = "category";

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
