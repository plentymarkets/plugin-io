<?php //strict

namespace IO\Constants;

/**
 * Class SessionStorageKeys
 * @package IO\Constants
 */
class SessionStorageKeys
{
	const DELIVERY_ADDRESS_ID      = "deliveryAddressId";
	const BILLING_ADDRESS_ID       = "billingAddressId";
	const CURRENCY                 = "currency";
    const NOTIFICATIONS            = "notifications";
    const LATEST_ORDER_ID          = "latestOrderId";
    const GUEST_EMAIL              = "guestEmail";
    const LAST_SEEN_ITEMS          = "lastSeenItems";
    const LAST_SEEN_MAX_COUNT      = "lastSeenMaxCount";
    const CROSS_SELLING_TYPE       = 'crossSellingType';
    const GUEST_WISHLIST           = 'guestWishList';
    const GUEST_WISHLIST_MIGRATION = 'guestWishListMigration';
    const ORDER_CONTACT_WISH       = 'orderContactWish';
}
