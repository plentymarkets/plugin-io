<?php //strict

namespace IO\Providers;

use IO\Helper\RouteConfig;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Http\Request;
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
            $api->resource('io/customer/contact/mail', 'ContactMailResource');
            $api->resource('io/customer/bank_data', 'ContactBankResource');
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
            $api->resource('io/cache/reset_template_cache', 'ResetTemplateCacheResource');
            $api->resource('io/shipping/country', 'ShippingCountryResource');
            $api->resource('io/live-shopping', 'LiveShoppingResource');
            $api->resource('io/facet', 'FacetResource');
		});

		/*
		 * STATIC ROUTES
		 */
		//Basket route
        if ( RouteConfig::isActive(RouteConfig::BASKET) )
        {
            // TODO: get slug from config
            $router->get('basket', 'IO\Controllers\BasketController@showBasket');
        }

        if ( RouteConfig::isActive(RouteConfig::CHECKOUT) )
        {
            //Checkout-confirm purchase route
            $router->get('checkout', 'IO\Controllers\CheckoutController@showCheckout');
        }
        else if ( RouteConfig::getCategoryId(RouteConfig::CHECKOUT) > 0 )
        {
            $router->get('checkout', 'IO\Controllers\CheckoutController@redirectCheckoutCategory');
        }

        if ( RouteConfig::isActive(RouteConfig::MY_ACCOUNT) )
        {
            //My-account route
            $router->get('my-account', 'IO\Controllers\MyAccountController@showMyAccount');
        }

		if ( RouteConfig::isActive(RouteConfig::CONFIRMATION) )
        {
            //Confiramtion route
            $router->get('confirmation/{orderId?}/{orderAccessKey?}', 'IO\Controllers\ConfirmationController@showConfirmation');

            $router->get('-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_py-/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_py_/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
            $router->get('_plentyShop__/akQQ{orderAccessKey}/idQQ{orderId}', 'IO\Controllers\ConfirmationEmailController@showConfirmation');
        }

		if ( RouteConfig::isActive(RouteConfig::LOGIN) )
        {
            //Login page route
            $router->get('login', 'IO\Controllers\LoginController@showLogin');
        }

		if ( RouteConfig::isActive(RouteConfig::REGISTER) )
        {
            //Register page route
            $router->get('register', 'IO\Controllers\RegisterController@showRegister');
            $router->get('registration', 'IO\Controllers\RegisterController@redirectRegister');
        }

		if ( RouteConfig::isActive(RouteConfig::PLACE_ORDER) )
        {
            // PaymentPlugin entry points
            // place the current order and redirect to /execute_payment
            $router->get('place-order', 'IO\Controllers\PlaceOrderController@placeOrder');

            // execute payment after order is created. PaymentPlugins can redirect to this route if order was created by the PaymentPlugin itself.
            $router->get('execute-payment/{orderId}/{paymentId?}', 'IO\Controllers\PlaceOrderController@executePayment')
                ->where('orderId', '[0-9]+');
        }

        if ( RouteConfig::isActive(RouteConfig::SEARCH) )
        {
            $router->get('search', 'IO\Controllers\ItemSearchController@showSearch');
            //Callisto Tag route
            $router->get('tag/{tagName}', 'IO\Controllers\ItemSearchController@redirectToSearch');
        }

        if ( RouteConfig::isActive(RouteConfig::HOME) )
        {
            //homepage route
            $router->get('', 'IO\Controllers\HomepageController@showHomepage');
        }

        if ( RouteConfig::isActive(RouteConfig::CANCELLATION_RIGHTS) )
        {
            //cancellation rights page
            $router->get('cancellation-rights', 'IO\Controllers\StaticPagesController@showCancellationRights');
        }

        if ( RouteConfig::isActive(RouteConfig::CANCELLATION_FORM) )
        {
            //cancellation rights page
            $router->get('cancellation-form', 'IO\Controllers\StaticPagesController@showCancellationForm');
        }

        if ( RouteConfig::isActive(RouteConfig::LEGAL_DISCLOSURE) )
        {
            //legal disclosure page
            $router->get('legal-disclosure', 'IO\Controllers\StaticPagesController@showLegalDisclosure');
        }

        if ( RouteConfig::isActive(RouteConfig::PRIVACY_POLICY))
        {
            //privacy policy page
            $router->get('privacy-policy', 'IO\Controllers\StaticPagesController@showPrivacyPolicy');
        }

        if ( RouteConfig::isActive(RouteConfig::TERMS_CONDITIONS) )
        {
            //terms and conditions page
            $router->get('gtc', 'IO\Controllers\StaticPagesController@showTermsAndConditions');
        }


        if( RouteConfig::isActive(RouteConfig::WISH_LIST) )
        {
            $router->get('wish-list', 'IO\Controllers\ItemWishListController@showWishList');
        }

        if( RouteConfig::isActive(RouteConfig::ORDER_RETURN) )
        {
            $router->get('returns/{orderId}', 'IO\Controllers\OrderReturnController@showOrderReturn');
        }

        if( RouteConfig::isActive(RouteConfig::ORDER_RETURN_CONFIRMATION) )
        {
            $router->get('return-confirmation', 'IO\Controllers\OrderReturnConfirmationController@showOrderReturnConfirmation');
        }

        if( RouteConfig::isActive(RouteConfig::CONTACT) )
        {
            //contact
            $router->get('contact', 'IO\Controllers\ContactController@showContact');
        }

        if( RouteConfig::isActive(RouteConfig::PASSWORD_RESET) )
        {
            $router->get('password-reset/{contactId}/{hash}', 'IO\Controllers\CustomerPasswordResetController@showReset');
        }

        if( RouteConfig::isActive(RouteConfig::ORDER_PROPERTY_FILE) )
        {
            $router->get('order-property-file/{hash1}', 'IO\Controllers\OrderPropertyFileController@downloadTempFile');
            $router->get('order-property-file/{hash1}/{hash2}', 'IO\Controllers\OrderPropertyFileController@downloadFile');
        }
        
        if( RouteConfig::isActive(RouteConfig::ORDER_DOCUMENT) )
        {
            $router->get('order-document/{documentId}', 'IO\Controllers\DocumentController@download');
        }

        
        if( RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_IN) )
        {
            $router->get('newsletter/subscribe/{authString}/{newsletterEmailId}', 'IO\Controllers\NewsletterOptInController@showOptInConfirmation');
        }
        
        if( RouteConfig::isActive(RouteConfig::NEWSLETTER_OPT_OUT) )
        {
            $router->get('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutController@showOptOut');
            $router->post('newsletter/unsubscribe', 'IO\Controllers\NewsletterOptOutConfirmationController@showOptOutConfirmation');
        }

        /*
         * ITEM ROUTES
         */
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
            $router->get('{slug}a-{itemId}', 'IO\Controllers\ItemController@showItemOld')
                ->where('slug', '.*')
                ->where('itemId', '[0-9]+');

            $router->get('a-{itemId}', 'IO\Controllers\ItemController@showItemFromAdmin')
                ->where('itemId', '[0-9]+');
        }

        /*
         * CATEGORY ROUTES
         */
        if ( RouteConfig::isActive(RouteConfig::CATEGORY) )
        {
            $router->get('{level1?}/{level2?}/{level3?}/{level4?}/{level5?}/{level6?}', 'IO\Controllers\CategoryController@showCategory');
        }
	}
}
