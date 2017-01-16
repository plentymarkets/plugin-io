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
			$api->resource('basket', 'BasketResource');
			$api->resource('basket/items', 'BasketItemResource');
			$api->resource('order', 'OrderResource');
			//$api->resource('checkout', 'CheckoutResource');
            $api->get('checkout', 'CheckoutResource@index');
            $api->post('checkout', 'CheckoutResource@store');
            $api->put('checkout', 'CheckoutResource@update');
			$api->resource('checkout/payment', 'CheckoutPaymentResource');
			$api->resource('customer', 'CustomerResource');
			$api->resource('customer/address', 'CustomerAddressResource');
			$api->resource('customer/login', 'CustomerAuthenticationResource');
			$api->resource('customer/logout', 'CustomerLogoutResource');
			$api->resource('customer/password', 'CustomerPasswordResource');
            $api->resource('variations', 'VariationResource');
            $api->resource('item/availability', 'AvailabilityResource');
            $api->resource('item/condition', 'ItemConditionResource');
            $api->resource('item/search', 'ItemSearchResource');
			$api->resource('customer/bank_data', 'ContactBankResource');
		});

		$enabledRoutes = explode(", ",  $config->get("PluginIO.routing.enabled_routes") );

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

		/*
		 * ITEM ROUTES
		 */
		if ( in_array("item", $enabledRoutes) || in_array("all", $enabledRoutes) )
        {
            $router->get('{itemId}/{variationId?}', 'IO\Controllers\ItemController@showItemWithoutName')
                   ->where('itemId', '[0-9]+')
                   ->where('variationId', '[0-9]+');

            $router->get('{itemName}/{itemId}/{variationId?}', 'IO\Controllers\ItemController@showItem')
                   ->where('itemId', '[0-9]+')
                   ->where('variationId', '[0-9]+');

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
