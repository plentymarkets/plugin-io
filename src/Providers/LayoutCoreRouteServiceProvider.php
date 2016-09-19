<?php //strict

namespace LayoutCore\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;
use Plenty\Plugin\Templates\Twig;

class LayoutCoreRouteServiceProvider extends RouteServiceProvider
{
	public function register()
	{
	}
	
	public function map(Router $router, ApiRouter $api)
	{
		$api->version(['v1'], ['namespace' => 'LayoutCore\Api\Resources'], function ($api)
		{
			$api->resource('basket', 'BasketResource');
			$api->resource('basket/items', 'BasketItemResource');
			$api->resource('item_variation_select', 'ItemVariationSelectResource');
			$api->resource('category_items_list', 'CategoryItemsListResource');
			$api->resource('order', 'OrderResource');
			$api->resource('checkout', 'CheckoutResource');
			$api->resource('customer', 'CustomerResource');
			$api->resource('customer/address', 'CustomerAddressResource');
			$api->resource('customer/login', 'CustomerAuthenticationResource');
			$api->resource('customer/logout', 'CustomerLogoutResource');
			$api->resource('customer/password', 'CustomerPasswordResource');
		});
		
		/*
		 * STATIC ROUTES
		 */
		//basket route
		// TODO: get slug from config
		$router->get('basket', 'LayoutCore\Controllers\BasketController@showBasket');
		
		//checkout-confirm buy route
		$router->get('checkout', 'LayoutCore\Controllers\CheckoutController@showCheckout');
		
		//my-account route
		$router->get('my-account', 'LayoutCore\Controllers\MyAccountController@showMyAccount');
		
		//confiramtion route
		$router->get('confirmation', 'LayoutCore\Controllers\ConfirmationController@showConfirmation');
		
		//guest route
		$router->get('guest', 'LayoutCore\Controllers\GuestController@showGuest');
		
		//login page route
		$router->get('login', 'LayoutCore\Controllers\LoginController@showLogin');
		
		//register page route
		$router->get('register', 'LayoutCore\Controllers\RegisterController@showRegister');
		
		/*
		 * ITEM ROUTES
		 */
		//$router->get('{itemName?}/{itemId}', 'LayoutCore\Controllers\ItemController@showItem')
		//->where('itemId', '[0-9]+');
		
		$router->get('{itemName?}/{itemId}/{variationId?}', 'LayoutCore\Controllers\ItemController@showItem')
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
