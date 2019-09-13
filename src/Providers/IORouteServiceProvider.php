<?php //strict

namespace IO\Providers;

use IO\Controllers\BasketController;
use IO\Controllers\CategoryController;
use IO\Controllers\CheckoutController;
use IO\Controllers\ContactController;
use IO\Controllers\LoginController;
use IO\Controllers\MyAccountController;
use IO\Controllers\StaticPagesController;
use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Services\SessionStorageService;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Route;
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
		$api->version(['v1'], ['namespace' => 'IO\Api\Resources'], function ($api)
		{
			$api->get('io/basket', 'BasketResource@index');
            $api->resource('io/basket/items', 'BasketItemResource');
            $api->get('io/order', 'OrderResource@index');
            $api->post('io/order', 'OrderResource@store');
			$api->get('io/order/paymentMethods', 'OrderPaymentResource@paymentMethodListForSwitch');
            $api->resource('io/order/payment', 'OrderPaymentResource');
            $api->resource('io/checkout/paymentId', 'CheckoutSetPaymentResource');
            $api->resource('io/checkout/shippingId', 'CheckoutSetShippingIdResource');
            $api->resource('io/order/contactWish', 'OrderContactWishResource');
            $api->resource('io/order/additional_information', 'OrderAdditionalInformationResource');
            $api->resource('io/order/return', 'OrderReturnResource');
            $api->resource('io/order/template', 'OrderTemplateResource');
            $api->resource('io/order/property/file', 'OrderPropertyFileResource');
            $api->get('io/checkout', 'CheckoutResource@index');
            $api->post('io/checkout', 'CheckoutResource@store');
            $api->put('io/checkout', 'CheckoutResource@update');
            $api->resource('io/category/description', 'CategoryDescriptionResource');
			$api->resource('io/checkout/payment', 'CheckoutPaymentResource');
			$api->resource('io/customer', 'CustomerResource');
			$api->resource('io/customer/address', 'CustomerAddressResource');
			$api->resource('io/customer/login', 'CustomerAuthenticationResource');
			$api->resource('io/customer/logout', 'CustomerLogoutResource');
			$api->resource('io/customer/password', 'CustomerPasswordResource');
            $api->resource('io/customer/password_reset', 'CustomerPasswordResetResource');
            $api->resource('io/customer/mail', 'CustomerMailResource');
            $api->resource('io/customer/contact/mail', 'ContactMailResource');
            $api->resource('io/customer/bank_data', 'ContactBankResource');
            $api->get('io/customer/order/list', 'CustomerOrderResource@index');
            $api->resource('io/customer/order/return', 'CustomerOrderReturnResource');
            $api->resource('io/customer/newsletter', 'CustomerNewsletterResource');
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
            $api->resource('io/facet', 'FacetResource');
            $api->resource('io/categorytree', 'CategoryTreeResource');

		});

		/** @var ShopUrls $shopUrls */
		$shopUrls = pluginApp(ShopUrls::class);

		// BASKET
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::BASKET,
            $shopUrls->basket,
            'IO\Controllers\BasketController@showBasket'
        );

        // CANCELLATION FORM
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CANCELLATION_FORM,
            $shopUrls->cancellationForm,
            'IO\Controllers\StaticPagesController@showCancellationForm'
        );

        // CANCELLATION RIGHTS
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CANCELLATION_RIGHTS,
            $shopUrls->cancellationRights,
            'IO\Controllers\StaticPagesController@showCancellationRights'
        );

        // CHANGE MAIL
        if( RouteConfig::isActive(RouteConfig::CHANGE_MAIL) )
        {
            $router->get('change-mail/{contactId}/{hash}', 'IO\Controllers\CustomerChangeMailController@show');
        }

        // CHECKOUT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CHECKOUT,
            $shopUrls->checkout,
            'IO\Controllers\CheckoutController@showCheckout'
        );

        // CONFIRMATION
        if ( RouteConfig::isActive(RouteConfig::CONFIRMATION) )
        {
            //Confirmation route
            $router->get('confirmation/{orderId?}/{orderAccessKey?}', 'IO\Controllers\ConfirmationController@showConfirmation');
            $router->get('-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_py-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_py_/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_plentyShop__/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
        }

        // CONTACT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::CONTACT,
            $shopUrls->contact,
            'IO\Controllers\ContactController@showContact'
        );

        // HOME
        if ( RouteConfig::isActive(RouteConfig::HOME) )
        {
            //homepage route
            $router->get('', 'IO\Controllers\HomepageController@showHomepage');
        }
        else if( in_array(RouteConfig::HOME, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::HOME) > 0)
        {
            $router->get('', 'IO\Controllers\HomepageController@showHomepageCategory');
        }

        // LEGAL DISCLOSURE
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::LEGAL_DISCLOSURE,
            $shopUrls->legalDisclosure,
            'IO\Controllers\StaticPagesController@showLegalDisclosure'
        );

        // LOGIN
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::LOGIN,
            $shopUrls->login,
            'IO\Controllers\LoginController@showLogin'
        );

        // MY ACCOUNT
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::MY_ACCOUNT,
            $shopUrls->myAccount,
            'IO\Controllers\MyAccountController@showMyAccount'
        );

        // NEWSLETTER OPT IN
        if( RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_IN) )
        {
            $router->get('newsletter/subscribe/{authString}/{newsletterEmailId}', 'IO\Controllers\NewsletterOptInController@showOptInConfirmation');
        }

        // NEWSLETTER OPT OUT
        if( RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_OUT) )
        {
            $router->get('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutController@showOptOut');
            $router->post('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutConfirmationController@showOptOutConfirmation');
        }
        else if( in_array(RouteConfig::NEWSLETTER_OPT_OUT, RouteConfig::getEnabledRoutes())
            && RouteConfig::getCategoryId(RouteConfig::NEWSLETTER_OPT_OUT) > 0
            && !$shopUrls->equals($shopUrls->newsletterOptOut, '/newsletter/unsubscribe'))
        {
            $router->get('/newsletter/unsubscribe', function() use ($shopUrls)
            {
                return pluginApp(CategoryController::class)->redirectToCategory($shopUrls->newsletterOptOut);
            });
        }

        // ORDER DOCUMENT
        if( RouteConfig::isActive(RouteConfig::ORDER_DOCUMENT) )
        {
            $router->get('order-document/preview/{documentId}', 'IO\Controllers\DocumentController@preview');
        }

        // ORDER PROPERTY FILE
        if( RouteConfig::isActive(RouteConfig::ORDER_PROPERTY_FILE) )
        {
            $router->get('order-property-file/{hash1}', 'IO\Controllers\OrderPropertyFileController@downloadTempFile');
            $router->get('order-property-file/{hash1}/{hash2}', 'IO\Controllers\OrderPropertyFileController@downloadFile');
        }

        // ORDER RETURN
        if( RouteConfig::isActive(RouteConfig::ORDER_RETURN) )
        {
            $router->get('returns/{orderId}', 'IO\Controllers\OrderReturnController@showOrderReturn');
        }

        // ORDER RETURN CONFIRMATION
        if( RouteConfig::isActive(RouteConfig::ORDER_RETURN_CONFIRMATION) )
        {
            $router->get('return-confirmation', 'IO\Controllers\OrderReturnConfirmationController@showOrderReturnConfirmation');
        }

        // PASSWORD RESET
        if( RouteConfig::isActive(RouteConfig::PASSWORD_RESET) )
        {
            $router->get('password-reset/{contactId}/{hash}', 'IO\Controllers\CustomerPasswordResetController@showReset');
        }

        // PLACE ORDER
        if ( RouteConfig::isActive(RouteConfig::PLACE_ORDER) )
        {
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
            'IO\Controllers\StaticPagesController@showPrivacyPolicy'
        );

        // REGISTER
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::REGISTER,
            $shopUrls->registration,
            'IO\Controllers\RegisterController@showRegister'
        );
        if ( RouteConfig::isActive(RouteConfig::REGISTER) )
        {
            //Register page route
            $router->get('registration', 'IO\Controllers\RegisterController@redirectRegister');
        }

        // SEARCH
        if ( RouteConfig::isActive(RouteConfig::SEARCH) )
        {
            $router->get('search', 'IO\Controllers\ItemSearchController@showSearch');
            //Callisto Tag route
            $router->get('tag/{tagName}', 'IO\Controllers\ItemSearchController@redirectToSearch');
        }

        // TERMS AND CONDITIONS
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::TERMS_CONDITIONS,
            $shopUrls->termsConditions,
            'IO\Controllers\StaticPagesController@showTermsAndConditions'
        );

        // WISH LIST
        $this->registerRedirectedRoute(
            $router,
            RouteConfig::WISH_LIST,
            $shopUrls->wishList,
            'IO\Controllers\ItemWishListController@showWishList'
        );

        // ITEM ROUTES
        if ( RouteConfig::isActive(RouteConfig::ITEM) )
        {
            $router->get('{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItemWithoutName')
                ->where('itemId', '[0-9]+')
                ->where('variationId', '[0-9]+');

            $router->get('{slug}_{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItem')
                ->where('slug', '[^_]+')
                ->where('itemId', '[0-9]+')
                ->where('variationId', '[0-9]+');

            //old webshop routes mapping
            $router->get('{slug}/a-{itemId}', 'IO\Controllers\ItemController@showItemOld')
                ->where('slug', '.*')
                ->where('itemId', '[0-9]+');

            $router->get('a-{itemId}', 'IO\Controllers\ItemController@showItemFromAdmin')
                ->where('itemId', '[0-9]+');
        }

        // CATEGORY ROUTES
        if ( RouteConfig::isActive(RouteConfig::CATEGORY) )
        {
            $router->get('{level1?}/{level2?}/{level3?}/{level4?}/{level5?}/{level6?}', 'IO\Controllers\CategoryController@showCategory');
        }

        // NOT FOUND
        if ( RouteConfig::isActive(RouteConfig::PAGE_NOT_FOUND) )
        {
            $router->get('{anything?}', 'IO\Controllers\StaticPagesController@showPageNotFound');
        }
	}

    /**
     * @param Router $router
     * @param $route
     * @param $shopUrl
     * @param string $legacyController
     * @throws \Plenty\Plugin\Routing\Exceptions\RouteReservedException
     */
	private function registerRedirectedRoute(Router $router, $route, $shopUrl, $legacyController = '')
    {
        if(in_array($route, RouteConfig::getEnabledRoutes()))
        {

            // legacy route is active
            if(RouteConfig::getCategoryId($route) <= 0)
            {
                // no category is assigned => bind legacy controller
                $router->get($route, $legacyController);
            }
            else
            {
                $router->get($route, function() use ($route)
                {
                    // category is assigend => redirect from legacy route to category url
                    // This will also check if the category url equals the legacy route to avoid endless loops
                    return pluginApp(CategoryController::class)->redirectToCategory(
                        RouteConfig::getCategoryId($route),
                        "/".$route
                    );
                });
            }
        }

        if ( !RouteConfig::isActive(RouteConfig::CATEGORY) && RouteConfig::getCategoryId($route) > 0 )
        {
            // register single category url if global category route is disabled
            $lang = pluginApp(SessionStorageService::class)->getLang();
            if(strpos($shopUrl, "/{$lang}/") === 0)
            {
                // remove language from shop url before registering the route
                $shopUrl = substr($shopUrl, strlen("/{$lang}/"));
            }
            $router->get(
                pluginApp(UrlQuery::class, ['path' => $shopUrl])->toRelativeUrl(false),
                function() use ($route)
                {
                    return pluginApp(CategoryController::class)->showCategoryById( RouteConfig::getCategoryId($route) );
                }
            );
        }
    }
}
