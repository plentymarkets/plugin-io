<?php //strict

namespace IO\Constants;

/**
 * Class SessionStorageKeys
 * @package IO\Constants
 */
class SessionStorageKeys
{
    //TODO VDI MOVE TO CORE MODULE
	const DELIVERY_ADDRESS_ID            = "deliveryAddressId";
	const BILLING_ADDRESS_ID             = "billingAddressId";
	const CURRENCY                       = "currency";
    const NOTIFICATIONS                  = "notifications";
    const LATEST_ORDER_ID                = "latestOrderId";
    const LAST_ACCESSED_ORDER            = "lastAccessedOrder";
    const GUEST_EMAIL                    = "guestEmail";
    const LAST_SEEN_ITEMS                = "lastSeenItems";
    const CROSS_SELLING_TYPE             = 'crossSellingType';
    const CROSS_SELLING_SORTING          = 'crossSellingSorting';
    const GUEST_WISHLIST                 = 'guestWishList';
    const GUEST_WISHLIST_MIGRATION       = 'guestWishListMigration';
    const ORDER_CONTACT_WISH             = 'orderContactWish';
    const ORDER_CUSTOMER_SIGN            = 'orderCustomerSign';
    const SHIPPING_PRIVACY_HINT_ACCEPTED = 'shippingPrivacyHintAccepted';
    const NEWSLETTER_SUBSCRIPTIONS       = 'newsletterSubscriptions';
    const READONLY_CHECKOUT              = 'readOnlyCheckout';
    const LAST_PLACE_ORDER_TRY           = 'lastPlaceOrderTry';
    const USE_VDI_SEARCH                 = 'useVdiSearch';
}
