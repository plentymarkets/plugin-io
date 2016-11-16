<?php //strict

namespace LayoutCore\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Class LayoutCoreRouteServiceProvider
 * @package LayoutCore\Providers
 */
class LayoutCoreRouteServiceProvider extends RouteServiceProvider
{
	public function register()
	{
	}

    /**
     * Define the map routes to templates or REST resources
     * @param Router $router
     * @param ApiRouter $api
     */
	public function map(Router $router, ApiRouter $api)
	{
		$api->version(['v1'], ['namespace' => 'LayoutCore\Api\Resources'], function ($api)
		{
			$api->resource('basket', 'BasketResource');
			$api->resource('basket/items', 'BasketItemResource');
			$api->resource('order', 'OrderResource');
			$api->resource('checkout', 'CheckoutResource');
			$api->resource('checkout/payment', 'CheckoutPaymentResource');
			$api->resource('customer', 'CustomerResource');
			$api->resource('customer/address', 'CustomerAddressResource');
			$api->resource('customer/login', 'CustomerAuthenticationResource');
			$api->resource('customer/logout', 'CustomerLogoutResource');
			$api->resource('customer/password', 'CustomerPasswordResource');
            $api->resource('variations', 'VariationResource');
            $api->resource('item/availability', 'AvailabilityResource');
            $api->resource('item/condition', 'ItemConditionResource');
			$api->resource('customer/bank_data', 'ContactBankResource');
		});

		/*
		 * STATIC ROUTES
		 */
		//Basket route
		// TODO: get slug from config
		$router->get('basket', 'LayoutCore\Controllers\BasketController@showBasket');

		//Checkout-confirm purchase route
		$router->get('checkout', 'LayoutCore\Controllers\CheckoutController@showCheckout');

		//My-account route
		$router->get('my-account', 'LayoutCore\Controllers\MyAccountController@showMyAccount');

		//Confiramtion route
		$router->get('confirmation', 'LayoutCore\Controllers\ConfirmationController@showConfirmation');

		//Guest route
		$router->get('guest', 'LayoutCore\Controllers\GuestController@showGuest');

		//Login page route
		$router->get('login', 'LayoutCore\Controllers\LoginController@showLogin');

		//Register page route
		$router->get('register', 'LayoutCore\Controllers\RegisterController@showRegister');

        // PaymentPlugin entry points
        // place the current order and redirect to /execute_payment
        $router->get('place-order', 'LayoutCore\Controllers\PlaceOrderController@placeOrder');

        // execute payment after order is created. PaymentPlugins can redirect to this route if order was created by the PaymentPlugin itself.
        $router->get('execute-payment/{orderId}/{paymentId?}', 'LayoutCore\Controllers\PlaceOrderController@executePayment')
            ->where('orderId', '[0-9]+');

		/*
		 * ITEM ROUTES
		 */
        $router->get('{itemId}/{variationId?}', 'LayoutCore\Controllers\ItemController@showItemWithoutName')
               ->where('itemId', '[0-9]+')
               ->where('variationId', '[0-9]+');

        $router->get('{itemName}/{itemId}/{variationId?}', 'LayoutCore\Controllers\ItemController@showItem')
               ->where('itemId', '[0-9]+')
               ->where('variationId', '[0-9]+');

		$router->get('a-{itemId}', 'LayoutCore\Controllers\ItemController@showItemFromAdmin')
		       ->where('itemId', '[0-9]+');


		/*
		 * CATEGORY ROUTES
		 */
		$router->get('{level1?}/{level2?}/{level3?}/{level4?}/{level5?}/{level6?}', 'LayoutCore\Controllers\CategoryController@showCategory');
	}
}
