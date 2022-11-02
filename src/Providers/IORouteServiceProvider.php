<?php //strict

namespace IO\Providers;

use IO\Controllers\CategoryController;
use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Class IORouteServiceProvider
 * @package IO\Providers
 */
class IORouteServiceProvider extends RouteServiceProvider
{
    public function register()
    {
    }

    /**
     * Define the map routes to templates or REST resources
     * @param Router $router
     * @param ApiRouter $api
     * @throws \Plenty\Plugin\Routing\Exceptions\RouteReservedException
     */
    public function map(Router $router, ApiRouter $api)
    {
        $api->version(['v1'], ['namespace' => 'IO\Api\Resources'], function (ApiRouter $api) {
            $api->get('io/basket', 'BasketResource@index');
            $api->resource('io/basket/items', 'BasketItemResource');
            $api->get('io/order', 'OrderResource@index');
            $api->get('io/order/paymentMethods', 'OrderPaymentResource@paymentMethodListForSwitch');
            $api->resource('io/order/template', 'OrderTemplateResource');
            $api->resource('io/order/property/file', 'OrderPropertyFileResource');
            $api->get('io/checkout', 'CheckoutResource@index');
            $api->resource('io/category/description', 'CategoryDescriptionResource');
            $api->resource('io/customer', 'CustomerResource');
            $api->resource('io/customer/login', 'CustomerAuthenticationResource');
            $api->resource('io/customer/logout', 'CustomerLogoutResource');
            $api->resource('io/customer/password', 'CustomerPasswordResource');
            $api->resource('io/customer/password_reset', 'CustomerPasswordResetResource');
            $api->resource('io/customer/mail', 'CustomerMailResource');
            $api->get('io/customer/order/list', 'CustomerOrderResource@index');
            $api->resource('io/customer/newsletter', 'CustomerNewsletterResource');
            $api->get('io/variations/map', 'VariationAttributeMapResource@index');
            $api->resource('io/variations', 'VariationResource');
            $api->resource('io/item/availability', 'AvailabilityResource');
            $api->resource('io/item/condition', 'ItemConditionResource');
            $api->resource('io/item/last_seen', 'ItemLastSeenResource');
            $api->get('io/item/search', 'ItemSearchResource@index');
            $api->get('io/item/search/autocomplete', 'ItemSearchAutocompleteResource@index');
            $api->resource('io/coupon', 'CouponResource');
            $api->resource('io/guest', 'GuestResource');
            $api->resource('io/category', 'CategoryItemResource');
            $api->resource('io/template', 'TemplateResource');
            $api->resource('io/localization/language', 'LanguageResource');
            $api->resource('io/itemWishList', 'ItemWishListResource');
            $api->resource('io/shipping/country', 'ShippingCountryResource');
            $api->resource('io/live-shopping', 'LiveShoppingResource');
            $api->get('io/categorytree/children', 'CategoryTreeResource@getChildren');
            $api->get('io/categorytree/template_for_children', 'CategoryTreeResource@getTemplateForChildren');
            $api->resource('io/categorytree', 'CategoryTreeResource');
            $api->get('io/session', 'SessionResource@index');
        });

        if (RouteConfig::isActive(RouteConfig::CONTACT_MAIL_API)) {
            $api->version(['v1'], ['namespace' => 'IO\Api\Resources'], function (ApiRouter $api) {
                $api->resource('io/customer/contact/mail', 'ContactMailResource');
                $api->resource('io/customer/contact/mail/file', 'ContactMailFileResource');
            });
        }

        $api->version(['v1'], ['namespace' => 'IO\Api\Resources', 'middleware' => ['csrf']], function (ApiRouter $api) {
            $api->post('io/order', 'OrderResource@store');
            $api->resource('io/order/payment', 'OrderPaymentResource');
            $api->resource('io/checkout/paymentId', 'CheckoutSetPaymentResource');
            $api->resource('io/customer/address', 'CustomerAddressResource');
            $api->resource('io/checkout/shippingId', 'CheckoutSetShippingIdResource');
            $api->resource('io/order/contactWish', 'OrderContactWishResource');
            $api->resource('io/order/return', 'OrderReturnResource');
            $api->post('io/checkout', 'CheckoutResource@store');
            $api->put('io/checkout', 'CheckoutResource@update');
            $api->resource('io/checkout/payment', 'CheckoutPaymentResource');
            $api->resource('io/customer/bank_data', 'ContactBankResource');
            $api->resource('io/customer/order/return', 'CustomerOrderReturnResource');
            $api->resource('io/order/additional_information', 'OrderAdditionalInformationResource');
        });

        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);

        // BASKET
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::BASKET,
            $shopUrls->basket,
            'IO\Controllers\BasketController@showBasket',
            'IO\Controllers\BasketController@redirect'
        );

        // CANCELLATION FORM
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CANCELLATION_FORM,
            $shopUrls->cancellationForm,
            'IO\Controllers\StaticPagesController@showCancellationForm',
            'IO\Controllers\StaticPagesController@redirectCancellationForm'
        );

        // CANCELLATION RIGHTS
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CANCELLATION_RIGHTS,
            $shopUrls->cancellationRights,
            'IO\Controllers\StaticPagesController@showCancellationRights',
            'IO\Controllers\StaticPagesController@redirectCancellationRights'
        );

        // CHANGE MAIL
        if (RouteConfig::isActive(RouteConfig::CHANGE_MAIL)) {
            $router->get('change-mail/{contactId}/{hash}', 'IO\Controllers\CustomerChangeMailController@show');
        } else if (in_array(RouteConfig::CHANGE_MAIL, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::CHANGE_MAIL) > 0
            && !$shopUrls->equals($shopUrls->changeMail, '/change-mail')
        ) {
            $router->get('change-mail/{contactId}/{hash}', 'IO\Controllers\CustomerChangeMailController@redirect');
        }

        if (RouteConfig::isActive(RouteConfig::MY_ACCOUNT)) {
            //My-account route
            $router->get('my-account', 'IO\Controllers\MyAccountController@showMyAccount');
        } else if (in_array(RouteConfig::MY_ACCOUNT, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::MY_ACCOUNT) > 0
            && !$shopUrls->equals($shopUrls->myAccount, '/my-account')) {
            // my-account-route is activated and category is linked and category url is not '/my-account'
            $router->get('my-account', 'IO\Controllers\MyAccountController@showMyAccount');
        }

        // CHECKOUT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CHECKOUT,
            $shopUrls->checkout,
            'IO\Controllers\CheckoutController@showCheckout',
            'IO\Controllers\CheckoutController@redirect'
        );

        // CONFIRMATION
        if (RouteConfig::isActive(RouteConfig::CONFIRMATION)
            || in_array(RouteConfig::CONFIRMATION, RouteConfig::getEnabledRoutes())
            || RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0)
        {
            $router->get('-/akQQ{orderAccessKey}/idQQ{orderId}.html', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_py-/akQQ{orderAccessKey}/idQQ{orderId}.html', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_py-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_py_/akQQ{orderAccessKey}/idQQ{orderId}.html', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_py_/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_plentyShop__/akQQ{orderAccessKey}/idQQ{orderId}.html', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
            $router->get('_plentyShop__/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation')->where('orderId', '\d+');
        }

        if (RouteConfig::isActive(RouteConfig::CONFIRMATION)) {
            //Confirmation route
            $router->get('confirmation/{orderId?}/{orderAccessKey?}', 'IO\Controllers\ConfirmationController@showConfirmation');
        } else if (in_array(RouteConfig::CONFIRMATION, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0
            && !$shopUrls->equals($shopUrls->confirmation, '/confirmation')) {
            // confirmation-route is activated and category is linked and category url is not '/confirmation'
            $router->get('confirmation/{orderId?}/{orderAccessKey?}', 'IO\Controllers\ConfirmationController@redirect');
        }

        if (RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0 && !RouteConfig::isActive(RouteConfig::CATEGORY)) {
            $this->registerRedirectedRoute(
                $router,
                RouteConfig::CONFIRMATION,
                $shopUrls->confirmation,
                'IO\Controllers\ConfirmationController@showContact',
                'IO\Controllers\ConfirmationController@redirect'
            );
        }

        if (RouteConfig::getCategoryId(RouteConfig::ORDER_RETURN) > 0 && !RouteConfig::isActive(RouteConfig::CATEGORY)) {
            $this->registerSingleCategoryRoute($router, RouteConfig::ORDER_RETURN, $shopUrls->returns);
        }

        // CONTACT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CONTACT,
            $shopUrls->contact,
            'IO\Controllers\ContactController@showContact',
            'IO\Controllers\ContactController@redirect'
        );

        // HOME
        if (RouteConfig::isActive(RouteConfig::HOME)) {
            //homepage route
            $router->get('', 'IO\Controllers\HomepageController@showHomepage');
        } else if (in_array(RouteConfig::HOME, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::HOME) > 0) {
            $router->get('', 'IO\Controllers\HomepageController@showHomepageCategory');
        }

        // LEGAL DISCLOSURE
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::LEGAL_DISCLOSURE,
            $shopUrls->legalDisclosure,
            'IO\Controllers\StaticPagesController@showLegalDisclosure',
            'IO\Controllers\StaticPagesController@redirectLegalDisclosure'
        );

        // LOGIN
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::LOGIN,
            $shopUrls->login,
            'IO\Controllers\LoginController@showLogin',
            'IO\Controllers\LoginController@redirect'
        );

        // MY ACCOUNT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::MY_ACCOUNT,
            $shopUrls->myAccount,
            'IO\Controllers\MyAccountController@showMyAccount',
            'IO\Controllers\MyAccountController@redirect'
        );

        // NEWSLETTER OPT IN
        if (RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_IN)) {
            $router->get('newsletter/subscribe/{authString}/{newsletterEmailId}', 'IO\Controllers\NewsletterOptInController@showOptInConfirmation');
        }

        // NEWSLETTER OPT OUT
        if (RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_OUT)) {
            $router->get('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutController@showOptOut');
            $router->post('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutConfirmationController@showOptOutConfirmation');
        } else if (in_array(RouteConfig::NEWSLETTER_OPT_OUT, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::NEWSLETTER_OPT_OUT) > 0
            && !$shopUrls->equals($shopUrls->newsletterOptOut, '/newsletter/unsubscribe')) {
            $router->get('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutController@redirect');
        }

        // ORDER DOCUMENT
        if (RouteConfig::isActive(RouteConfig::ORDER_DOCUMENT)) {
            $router->get('order-document/preview/{documentId}', 'IO\Controllers\DocumentController@preview');
        }

        // ORDER PROPERTY FILE
        if (RouteConfig::isActive(RouteConfig::ORDER_PROPERTY_FILE)) {
            $router->get('order-property-file/{hash1}', 'IO\Controllers\OrderPropertyFileController@downloadTempFile');
            $router->get('order-property-file/{hash1}/{hash2}', 'IO\Controllers\OrderPropertyFileController@downloadFile');
        }

        // ORDER RETURN
        if (RouteConfig::isActive(RouteConfig::ORDER_RETURN)) {
            $router->get('returns/{orderId}/{orderAccessKey?}', 'IO\Controllers\OrderReturnController@showOrderReturn');
        } else if (in_array(RouteConfig::ORDER_RETURN, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::ORDER_RETURN) > 0
            && !$shopUrls->equals($shopUrls->returns, '/returns')) {
            $router->get('returns/{orderId}/{orderAccessKey?}', 'IO\Controllers\OrderReturnController@redirect');

        }

        // ORDER RETURN CONFIRMATION
        if (RouteConfig::isActive(RouteConfig::ORDER_RETURN_CONFIRMATION)) {
            $router->get('return-confirmation', 'IO\Controllers\OrderReturnConfirmationController@showOrderReturnConfirmation');
        }

        // PASSWORD RESET
        if (RouteConfig::isActive(RouteConfig::PASSWORD_RESET)) {
            $router->get('password-reset/{contactId}/{hash}', 'IO\Controllers\CustomerPasswordResetController@showReset');
        } else if (in_array(RouteConfig::PASSWORD_RESET, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::PASSWORD_RESET) > 0
            && !$shopUrls->equals($shopUrls->passwordReset, '/password-reset')
        ) {
            $router->get('password-reset/{contactId}/{hash}', 'IO\Controllers\CustomerPasswordResetController@redirect');
        }

        // PLACE ORDER
        if (RouteConfig::isActive(RouteConfig::PLACE_ORDER)) {
            // PaymentPlugin entry points
            // place the current order and redirect to /execute_payment
            $router->get('place-order', 'IO\Controllers\PlaceOrderController@placeOrder');

            // execute payment after order is created. PaymentPlugins can redirect to this route if order was created by the PaymentPlugin itself.
            $router->get('execute-payment/{orderId}/{paymentId?}', 'IO\Controllers\PlaceOrderController@executePayment')
                ->where('orderId', '[0-9]+');
        }

        // PRIVACY POLICY
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::PRIVACY_POLICY,
            $shopUrls->privacyPolicy,
            'IO\Controllers\StaticPagesController@showPrivacyPolicy',
            'IO\Controllers\StaticPagesController@redirectPrivacyPolicy'
        );

        // REGISTER
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::REGISTER,
            $shopUrls->registration,
            'IO\Controllers\RegisterController@showRegister',
            'IO\Controllers\RegisterController@redirect'
        );
        if (RouteConfig::isActive(RouteConfig::REGISTER)) {
            //Register page route
            $router->get('registration', 'IO\Controllers\RegisterController@redirectRegister');
        }

        // SEARCH
        if (RouteConfig::isActive(RouteConfig::SEARCH) || in_array(RouteConfig::SEARCH, RouteConfig::getEnabledRoutes())
            || RouteConfig::getCategoryId(RouteConfig::SEARCH) > 0) {
            //Callisto Tag route
            $router->get('tag/{tagName}', 'IO\Controllers\ItemSearchController@redirectToSearch');
        }
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::SEARCH,
            $shopUrls->search,
            'IO\Controllers\ItemSearchController@showSearch',
            'IO\Controllers\ItemSearchController@redirectToSearch'
        );

        // TERMS AND CONDITIONS
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::TERMS_CONDITIONS,
            $shopUrls->termsConditions,
            'IO\Controllers\StaticPagesController@showTermsAndConditions',
            'IO\Controllers\StaticPagesController@redirectTermsAndConditions'
        );

        // WISH LIST
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::WISH_LIST,
            $shopUrls->wishList,
            'IO\Controllers\ItemWishListController@showWishList',
            'IO\Controllers\ItemWishListController@redirect'
        );

        // ITEM ROUTES
        if (RouteConfig::isActive(RouteConfig::ITEM)) {
            $router->get('{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItemWithoutName')
                ->where('itemId', '[0-9]+')
                ->where('variationId', '[0-9]+');

            $router->get('{slug}{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItem')
                ->where('slug', '[^_]*[^\/]_')
                ->where('itemId', '[0-9]+')
                ->where('variationId', '[0-9]+');

            //old webshop routes mapping
            $router->get('{slug}/a-{itemId}', 'IO\Controllers\ItemController@showItemOld')
                ->where('slug', '.*')
                ->where('itemId', '[0-9]+');

            $router->get('a-{itemId}', 'IO\Controllers\ItemController@showItemFromAdmin')
                ->where('itemId', '[0-9]+');
        }

        // TAGS
        if (RouteConfig::isActive(RouteConfig::TAGS)) {
            $router->get('{tagName}_t{tagId}', 'IO\Controllers\TagController@showItemByTag')
                ->where('tagName', '[^\/]*')
                ->where('tagId', '[0-9]+');
        }

        // CATEGORY ROUTES
        if (RouteConfig::isActive(RouteConfig::CATEGORY)) {
            $categoryRoute = $router->get('{level1?}/{level2?}/{level3?}/{level4?}/{level5?}/{level6?}', 'IO\Controllers\CategoryController@showCategory');

            if (RouteConfig::passThroughBlogRoutes()) {
                // do not catch legacy blog-routes
                $categoryRoute->where('level1', '(?:(?!blog)[^\/]*|[^\/]*(?<!blog))');
            }
        }

        // NOT FOUND
        if (in_array(RouteConfig::PAGE_NOT_FOUND, RouteConfig::getEnabledRoutes())) {
            $fallbackRoute = $router->get('{level1?}/{anything?}', 'IO\Controllers\StaticPagesController@getPageNotFoundStatusResponse');
            if (RouteConfig::passThroughBlogRoutes()) {
                // do not catch legacy blog-routes
                $fallbackRoute
                    ->where('level1', '(?:(?!blog)[^\/]*|[^\/]*(?<!blog))')
                    ->where('anything', '.*');
            } else {
                $fallbackRoute->where('level1', '.*');
            }
        }
    }

    /**
     * @param Router $router
     * @param string $route
     * @param string $shopUrl
     * @param string $legacyController
     * @param string $redirectController
     * @throws \Plenty\Plugin\Routing\Exceptions\RouteReservedException
     */
    private function registerRedirectedRoute(
        Router $router,
        $route,
        $shopUrl,
        $legacyController,
        $redirectController
    )
    {
        if (in_array($route, RouteConfig::getEnabledRoutes())) {

            // legacy route is active
            if (RouteConfig::getCategoryId($route) <= 0) {
                // no category is assigned => bind legacy controller
                $router->get($route, $legacyController);
            } else {
                $router->get($route, $redirectController);
            }
        }

        if (!RouteConfig::isActive(RouteConfig::CATEGORY) && RouteConfig::getCategoryId($route) > 0 && !empty($shopUrl)) {
            $this->registerSingleCategoryRoute($router, $route, $shopUrl);
        }
    }

    private function registerSingleCategoryRoute(Router $router, $route, $shopUrl)
    {
        // register single category url if global category route is disabled
        $lang = Utils::getLang();
        if (strpos($shopUrl, "/{$lang}/") === 0) {
            // remove language from shop url before registering the route
            $shopUrl = substr($shopUrl, strlen("/{$lang}/"));
        }

        if ($shopUrl !== '/' && $shopUrl !== '/' . $lang) {
            $router->get(
                Utils::makeRelativeUrl($shopUrl, false),
                function () use ($route) {
                    /** @var CategoryController $categoryController */
                    $categoryController = pluginApp(CategoryController::class);
                    return $categoryController->showCategoryById(RouteConfig::getCategoryId($route));
                }
            );
        }
    }
}
