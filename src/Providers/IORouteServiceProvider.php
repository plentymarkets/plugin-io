<?php //strict

namespace IO\Providers;

use Plenty\Plugin\ConfigRepository;
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
     */
	public function map(Router $router, ApiRouter $api, ConfigRepository $config)
	{
		$api->version(['v1'], ['namespace' => 'IO\Api\Resources'], function ($api)
		{
			$api->get('io/basket', 'BasketResource@index');
			$api->resource('io/basket/items', 'BasketItemResource');
            $api->get('io/order', 'OrderResource@index');
            $api->post('io/order', 'OrderResource@store');
			$api->get('io/order/paymentMethods', 'OrderPaymentResource@paymentMethodListForSwitch');
            $api->resource('io/order/payment', 'OrderPaymentResource');
            $api->get('io/checkout', 'CheckoutResource@index');
            $api->post('io/checkout', 'CheckoutResource@store');
            $api->put('io/checkout', 'CheckoutResource@update');
			$api->resource('io/checkout/payment', 'CheckoutPaymentResource');
			$api->resource('io/customer', 'CustomerResource');
			$api->resource('io/customer/address', 'CustomerAddressResource');
			$api->resource('io/customer/login', 'CustomerAuthenticationResource');
			$api->resource('io/customer/logout', 'CustomerLogoutResource');
			$api->resource('io/customer/password', 'CustomerPasswordResource');
            $api->resource('io/variations', 'VariationResource');
            $api->resource('io/item/availability', 'AvailabilityResource');
            $api->resource('io/item/condition', 'ItemConditionResource');
            $api->get('io/item/search', 'ItemSearchResource@index');
            $api->get('io/item/search/autocomplete', 'ItemSearchAutocompleteResource@index');
			$api->resource('io/customer/bank_data', 'ContactBankResource');
			$api->resource('io/coupon', 'CouponResource');
            $api->resource('io/guest', 'GuestResource');
            $api->resource('io/category', 'CategoryItemResource');
            $api->resource('io/template', 'TemplateResource');
		});

		$enabledRoutes = explode(", ",  $config->get("IO.routing.enabled_routes") );

		/*
		 * STATIC ROUTES
		 */
		//Basket route
        if ( in_array("basket", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            // TODO: get slug from config
            $router->get('basket', 'IO\Controllers\BasketController@showBasket');
        }

        if ( in_array("checkout", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //Checkout-confirm purchase route
            $router->get('checkout', 'IO\Controllers\CheckoutController@showCheckout');
        }

        if ( in_array("my-account", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //My-account route
            $router->get('my-account', 'IO\Controllers\MyAccountController@showMyAccount');
        }

		if ( in_array("confirmation", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //Confiramtion route
            $router->get('confirmation', 'IO\Controllers\ConfirmationController@showConfirmation');
        }

		if ( in_array("guest", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //Guest route
            $router->get('guest', 'IO\Controllers\GuestController@showGuest');
        }

		if ( in_array("login", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //Login page route
            $router->get('login', 'IO\Controllers\LoginController@showLogin');
        }

		if ( in_array("register", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            //Register page route
            $router->get('register', 'IO\Controllers\RegisterController@showRegister');
        }

		if ( in_array("place-order", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            // PaymentPlugin entry points
            // place the current order and redirect to /execute_payment
            $router->get('place-order', 'IO\Controllers\PlaceOrderController@placeOrder');

            // execute payment after order is created. PaymentPlugins can redirect to this route if order was created by the PaymentPlugin itself.
            $router->get('execute-payment/{orderId}/{paymentId?}', 'IO\Controllers\PlaceOrderController@executePayment')
                ->where('orderId', '[0-9]+');
        }

        if ( in_array("search", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            $router->get('search', 'IO\Controllers\ItemSearchController@showSearch');
        }

        if ( in_array("home", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            //homepage route
            $router->get('', 'IO\Controllers\HomepageController@showHomepage');
        }

        if ( in_array("cancellation-rights", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            //cancellation rights page
            $router->get('cancellation-rights', 'IO\Controllers\StaticPagesController@showCancellationRights');
        }

        if ( in_array("legal-disclosure", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            //legal disclosure page
            $router->get('legal-disclosure', 'IO\Controllers\StaticPagesController@showLegalDisclosure');
        }

        if ( in_array("privacy-policy", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            //privacy policy page
            $router->get('privacy-policy', 'IO\Controllers\StaticPagesController@showPrivacyPolicy');
        }

        if ( in_array("gtc", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            //terms and conditions page
            $router->get('gtc', 'IO\Controllers\StaticPagesController@showTermsAndConditions');
        }
        
		/*
		 * ITEM ROUTES
		 */
        if ( in_array("item", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            $router->get('{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItemWithoutName')
                   ->where('itemId', '[0-9]+')
                   ->where('variationId', '[0-9]+');
            
            $router->get('{slug}_{itemId}_{variationId?}', 'IO\Controllers\ItemController@showItem')
                   ->where('slug', '[^_]+')
                   ->where('itemId', '[0-9]+')
                   ->where('variationId', '[0-9]+');
            
            //old webshop routes mapping
            $router->get('{slug}a-{itemId}', 'IO\Controllers\ItemController@showItemOld')
                   ->where('slug', '.*')
                   ->where('itemId', '[0-9]+');
            
            $router->get('a-{itemId}', 'IO\Controllers\ItemController@showItemFromAdmin')
                   ->where('itemId', '[0-9]+');
        }
        
        /*
		 * CATEGORY ROUTES
		 */
        if ( in_array("category", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            $router->get('{level1?}/{level2?}/{level3?}/{level4?}/{level5?}/{level6?}', 'IO\Controllers\CategoryController@showCategory');
        }
	}
}
