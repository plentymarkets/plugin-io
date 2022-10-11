<?php //strict

namespace IO\Services;

use IO\Constants\LogLevel;
use IO\Helper\Utils;
use Plenty\Modules\Accounting\Contracts\DetermineShopCountryContract;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Basket\Exceptions\BasketItemQuantityCheckException;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Modules\Item\VariationDescription\Models\VariationDescription;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\EUCountryCodesServiceContract;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\Helpers\PropertyHelper;
use Plenty\Modules\Webshop\Helpers\UnitUtils;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class BasketService
 *
 * This service class contains various methods used for manipulating the customers basket.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class BasketService
{
    use Loggable;

    /**
     * @var BasketItemRepositoryContract This repository is used to manipulate basketItems
     * @see \Plenty\Modules\Basket\Models\BasketItem
     */
    private $basketItemRepository;

    /**
     * @var BasketRepositoryContract This repository is used to manipulate the customers basket
     */
    private $basketRepository;

    /**
     * @var Checkout This service is used to manipulate the checkout
     */
    private $checkout;

    /**
     * @var VatService This service provides methods for VAT related tasks
     */
    private $vatService;

    /**
     * @var ContactRepositoryContract $contactRepository This repository is used to manipulate Contacts
     */
    private $contactRepository;

    /**
     * @var CouponService $couponService This service provides functionality related to coupons
     */
    private $couponService;

    /**
     * @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository This repository is used to read webstore configuration
     */
    private $webstoreConfigurationRepository;

    /**
     * @var SessionStorageRepositoryContract $sessionStorageRepository This repository is used to read and write data of the session
     */
    private $sessionStorageRepository;

    /**
     * @var BasketItem[] Contains all current BasketItems
     */
    private $basketItems;

    /**
     * @var string Unused property
     * @deprecated
     */
    private $template = '';

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     * @param VatService $vatService
     * @param ContactRepositoryContract $contactRepository
     * @param BasketRepositoryContract $basketRepository
     * @param VatInitContract $vatInitService
     * @param CouponService $couponService
     * @param WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     */
    public function __construct(
        BasketItemRepositoryContract $basketItemRepository,
        Checkout $checkout,
        VatService $vatService,
        ContactRepositoryContract $contactRepository,
        BasketRepositoryContract $basketRepository,
        VatInitContract $vatInitService,
        CouponService $couponService,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository,
        SessionStorageRepositoryContract $sessionStorageRepository
    )
    {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout = $checkout;
        $this->vatService = $vatService;
        $this->contactRepository = $contactRepository;
        $this->basketRepository = $basketRepository;
        $this->couponService = $couponService;
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
        $this->sessionStorageRepository = $sessionStorageRepository;

        if (!$vatInitService->isInitialized()) {
            $vat = $this->vatService->getVat();
        }
    }

    /**
     * Set the template property. Different templates need different data.
     *
     * @param string $template
     * @deprecated
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * Gets the basket object with relevant data for the template renderer
     *
     * @return array
     */
    public function getBasketForTemplate(): array
    {
        /** @var EUCountryCodesServiceContract $euCountryService */
        $euCountryService = pluginApp(EUCountryCodesServiceContract::class);

        /** @var DetermineShopCountryContract $determineShopCountry */
        $determineShopCountry = pluginApp(DetermineShopCountryContract::class);

        /** @var ItemWishListService $wishListService */
        $wishListService = pluginApp(ItemWishListService::class);

        $basket = $this->getBasket()->toArray();

        $basket["itemQuantity"] = $this->getBasketQuantity();

        if ($basket["itemQuantity"] > 0) {
            $basket["totalVats"] = $this->getTotalVats();
        } else {
            $basket["totalVats"] = [];
        }

        $order = $this->sessionStorageRepository->getOrder();

        $isNet = false;
        if (!is_null($order)) {
            $isNet = $order->isNet;
        }

        $couponValidation = $order->couponCodeValidation;

        if (!is_null($couponValidation)) {

            /** @var CouponCampaignRepositoryContract $campaignRepository */
            $campaignRepository = pluginApp(CouponCampaignRepositoryContract::class);
            $campaign = $campaignRepository->findById($couponValidation->campaignId);

            if ($isNet) {
                $basket['couponDiscount'] = $couponValidation->salesDiscountNet;
            }

            if ($this->couponService->effectsOnShippingCosts($campaign)) {
                $basket['shippingAmountNet'] -= $couponValidation->shippingDiscountNet;
                $basket['shippingAmount'] -= $couponValidation->shippingDiscount;
            }
        }

        if (count($basket['totalVats']) <= 0 && $isNet) {
            $basket["itemSum"] = $basket["itemSumNet"];
            $basket["basketAmount"] = $basket["basketAmountNet"];
            $basket["shippingAmount"] = $basket["shippingAmountNet"];

        }
        $basket['subAmount'] = $this->getSubAmount($basket["basketAmountNet"]);


        $basket = $this->couponService->checkCoupon($basket);
        $determineShopCountry->initByPlentyId(Utils::getPlentyId());

        $basket["isExportDelivery"] = $euCountryService->isExportDelivery(
            $basket["shippingCountryId"] ?? $this->webstoreConfigurationRepository->getDefaultShippingCountryId()
        );

        $basket["shopCountryId"] = $determineShopCountry->getCountryId();

        $basket["itemWishListIds"] = $wishListService->getItemWishList();

        return $basket;
    }

    /**
     * Return subAmount
     */
    private function getSubAmount($basketAmountNet): float
    {
        /** @var BasketRepositoryContract $basketRepository */
        $basketRepository = pluginApp(BasketRepositoryContract::class);

        if ($this->webstoreConfigurationRepository->getWebstoreConfiguration()->useVariationOrderProperties) {
            $taxFreeAmount = $basketRepository->getTaxFreeAmount();
        } else {
           $basketItems = $this->getBasketItems();
           $taxFreeAmount = $basketRepository->getTaxFreeAmount($basketItems);
        }

        return $basketAmountNet - $taxFreeAmount;
    }

    /**
     * Return the basket model
     *
     * @return Basket
     */
    public function getBasket(): Basket
    {
        /** @var BasketRepositoryContract $basketRepository */
        $basketRepository = pluginApp(BasketRepositoryContract::class);

        /** @var  CheckoutRepositoryContract $checkoutRepository */
        $checkoutRepository = pluginApp(CheckoutRepositoryContract::class);

        $basket = $basketRepository->load();
        $basket->currency = $checkoutRepository->getCurrency();
        return $basket;
    }

    /**
     * Gets all VATs applied to the basket
     *
     * @return array
     */
    public function getTotalVats(): array
    {
        return $this->vatService->getCurrentTotalVats();
    }

    /**
     * Gets the total quantity of all basket items.
     *
     * @return float|int
     */
    public function getBasketQuantity()
    {
        if (!is_array($this->basketItems)) {
            return $this->basketItemRepository->getBasketItemQuantity();
        }

        $itemQuantity = 0;
        foreach ($this->getBasketItemsRaw() as $item) {
            if ($item->variationId > 0) {
                $itemQuantity += $item->quantity;
            }
        }

        return $itemQuantity;
    }

    /**
     * List the basket items
     *
     * @return array
     */
    public function getBasketItems(): array
    {
        $basketItems = $this->getBasketItemsRaw();
        $basketItemData = $this->getBasketItemData($basketItems);
        $basketItems = $this->addVariationData($basketItems, $basketItemData);

        return $basketItems;
    }

    /**
     * List the basket items for order
     *
     * @return array
     */
    public function getBasketItemsForOrder(): array
    {
        $basketItems = $this->getBasketItemsRaw();
        $basketItemData = $this->getOrderItemData($basketItems);
        $basketItems = $this->addVariationData($basketItems, $basketItemData);

        return $basketItems;
    }

    /**
     * Get basket items with all relevant data for the template renderer.
     *
     * @param string $template Unused parameter (legacy purposes)
     * @param bool $appendItemData Flag for adding item data to the basket items
     * @return array
     */
    public function getBasketItemsForTemplate(string $template = '', $appendItemData = true): array
    {
        $basketItems = $this->getBasketItemsRaw();
        return $this->processBasketItems($basketItems, $appendItemData);
    }

    protected function processBasketItems($basketItems, $appendItemData = true)
    {
        $basketItemData = $appendItemData ? $this->getBasketItemData($basketItems) : [];
        $basketItems = $this->addVariationData($basketItems, $basketItemData, true);
        $basketItems = $this->filterSetItems($basketItems);
        $basketItems = $this->filterVariationOrderProperties($basketItems);

        $basketItems = array_map(
            function ($basketItem) {
                return $this->reduceBasketItem($basketItem);
            },
            $basketItems
        );

        if ($appendItemData) {
            $basketItems = $this->removeInactiveBasketItems($basketItems);
        }

        return $basketItems;
    }

    /**
     * Remove basket items not having a valid language for the current basket configuration.
     *
     * @param string $language The language in format ISO-639-1
     */
    public function checkBasketItemsLang($language = '')
    {
        $basketItems = $this->getBasketItemsRaw();

        // Don't check if no basket items.
        if (count($basketItems) <= 0) {
            return;
        }

        $basketItemData = $this->getBasketItemData($basketItems, $language);
        $basketItems = $this->addVariationData($basketItems, $basketItemData, true);

        $showWarning = [];

        $basketItemIds = [];
        foreach ($basketItems as $basketItem) {
            if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_VARIATION_ORDER_PROPERTY) {
                continue;
            } elseif ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT) {
                $basketItemId = $basketItem['itemBundleRowId'];
            } else {
                $basketItemId = $basketItem['id'];
            }

            $delete = false;
            if (!isset($basketItem['variation']['id'])) {
                $showWarning[] = 9;
                $delete = true;
            } elseif (!$this->hasTexts($basketItem['variation']['data'])) {
                $showWarning[] = 10;
                $delete = true;
            }

            if ($delete && !in_array($basketItemId, $basketItemIds)) {
                $basketItemIds[] = $basketItemId;
            }
        }

        if (count($basketItemIds)) {
            foreach ($basketItemIds as $basketItemId) {
                $this->deleteBasketItem($basketItemId);
            }
        }

        if (count($showWarning) > 0) {
            $showWarning = array_unique($showWarning);

            foreach ($showWarning as $warning) {
                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);
                $notificationService->warn(LogLevel::WARN, $warning);
            }
        }
    }

    /**
     * Remove basket items not having a valid price for the current basket configuration (referrer, currency,...)
     *
     * @deprecated Use checkBasketItemsByPrice instead
     */
    public function checkBasketItemsCurrency()
    {
        if ($this->checkBasketItemsByPrice() > 0) {
            /** @var NotificationService $notificationService */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->warn(LogLevel::WARN, 14);
        }
    }

    /**
     * Remove basket items not having a valid price for the current basket configuration (referrer, currency,...)
     *
     * @return int number of removed basket items.
     */
    public function checkBasketItemsByPrice()
    {
        $basketItems = $this->getBasketItemsRaw();

        // Don't check if no basket items.
        if (count($basketItems) <= 0) {
            return 0;
        }

        $basketItemData = $this->getBasketItemData($basketItems);
        $basketItems = $this->addVariationData($basketItems, $basketItemData, true);

        $basketItemIds = [];
        foreach ($basketItems as $basketItem) {
            if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_VARIATION_ORDER_PROPERTY) {
                continue;
            } elseif ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT) {
                $basketItemId = $basketItem['itemBundleRowId'];
            } else {
                $basketItemId = $basketItem['id'];
            }

            $delete = false;
            if (!isset($basketItem['variation']['data']['prices']['default']) || is_null($basketItem['variation']['data']['prices']['default'])) {
                $delete = true;
            }

            if ($delete && !in_array($basketItemId, $basketItemIds)) {
                $basketItemIds[] = $basketItemId;
            }
        }

        if (count($basketItemIds)) {
            foreach ($basketItemIds as $basketItemId) {
                $this->deleteBasketItem($basketItemId);
            }
        }

        return count($basketItemIds);
    }

    /**
     * Get a basket item
     *
     * @param int|BasketItem $basketItemId The unique id of the basketItem
     * @param bool $appendVariation Flag for appending itemData to the BasketItem
     * @return array
     */
    public function getBasketItem($basketItemId, $appendVariation = true)
    {
        if ($basketItemId instanceof BasketItem) {
            $basketItem = $basketItemId;
        } else {
            $basketItem = $this->basketItemRepository->findOneById($basketItemId);
        }

        if ($basketItem === null) {
            return array();
        }

        if ($basketItem->itemType === BasketItem::BASKET_ITEM_TYPE_ITEM_SET) {
            $basketItem->price = $basketItem->givenPrice + $basketItem->attributeTotalMarkup;
        }

        $basketItems = [$basketItem];
        if(count($basketItem->basketItemVariationProperties) > 0) {
            foreach($basketItem->basketItemVariationProperties as $basketItemVariationProperty) {
                $basketItems[] = $basketItemVariationProperty;
            }
        }
        $basketItems = $this->processBasketItems($basketItems, $appendVariation);

        $basketItem = array_pop($basketItems);
        if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET && (!isset($basketItem['setComponents']) || !count(
                    $basketItem['setComponents']
                ))) {
            $basketItem['setComponents'] = array_values($this->getSetComponents($basketItem['id'], $appendVariation));
        }

        return $basketItem;
    }

    /**
     * Add an item to the basket or update the basket
     *
     * @param array $data Contains the basket item
     * @return array
     */
    public function addBasketItem(array $data): array
    {
        if ($this->webstoreConfigurationRepository->getWebstoreConfiguration()->dontSplitItemBundle === 0) {
            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp(ItemSearchService::class);

            /** @var VariationSearchFactory $searchFactory */
            $searchFactory = pluginApp(VariationSearchFactory::class);

            $item = $itemSearchService->getResults(
                [
                    $searchFactory
                        ->hasVariationId($data['variationId'])
                        ->withBundleComponents()
                        ->withResultFields(
                            [
                                'variation.bundleType'
                            ]
                        )
                ]
            )[0];

            if ($item['documents']['0']['data']['variation']['bundleType'] === 'bundle') {
                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);

                $notificationService->warn('Item bundle split', 5);

                foreach ($item['documents']['0']['data']['bundleComponents'] as $bundleComponent) {
                    $basketData = [];

                    $basketData['variationId'] = $bundleComponent['data']['variation']['id'];
                    $basketData['quantity'] = $bundleComponent['quantity'] * ($data['quantity'] ?? 1);
                    $basketData['template'] = $data['template'];

                    $componentData = $this->addDataToBasket($basketData);
                    if (count($componentData)) {
                        return $componentData;
                    }
                }
                return [];
            }
        }
        return $this->addDataToBasket($data);
    }

    /**
     * Update a basket item
     *
     * @param int $basketItemId The unique id of the basket item
     * @param array $data Contains the updated basket item
     * @return array
     */
    public function updateBasketItem(int $basketItemId, array $data): array
    {
        $data['id'] = $basketItemId;
        try {
            $this->basketItemRepository->updateBasketItem($basketItemId, $data);
        } catch (BasketItemQuantityCheckException $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_updateItemQuantityCheckFailed',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            switch ($e->getCode()) {
                case BasketItemQuantityCheckException::DID_REACH_MAXIMUM_QUANTITY_FOR_ITEM:
                    $code = 112;
                    break;
                case BasketItemQuantityCheckException::DID_REACH_MAXIMUM_QUANTITY_FOR_VARIATION:
                    $code = 113;
                    break;
                case BasketItemQuantityCheckException::DID_NOT_REACH_MINIMUM_QUANTITY_FOR_VARIATION:
                    $code = 114;
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code];
        } catch (BasketItemCheckException $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_updateItemCheckFailed',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            switch ($e->getCode()) {
                case BasketItemCheckException::VARIATION_NOT_FOUND:
                    $code = 110;
                    break;
                case BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATION:
                    $code = 111;
                    $placeholder = ['stock' => $e->getStockNet()];
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code, 'placeholder' => $placeholder];
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_cannotAddItem',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            return ["code" => $e->getCode()];
        }
        return $this->getBasketItemsForTemplate();
    }

    /**
     * Delete an item from the basket
     *
     * @param int $basketItemId The unique id of the basketItem
     */
    public function deleteBasketItem(int $basketItemId)
    {
        $this->basketItemRepository->removeBasketItem($basketItemId);
    }

    /**
     * Check whether the item is already in the basket
     * @param array $data Contains the basket item to search for
     * @return null|BasketItem
     */
    public function findExistingOneByData(array $data)
    {
        return $this->basketItemRepository->findExistingOneByData($data);
    }

    /**
     * Reset basket after execute payment / order created
     */
    public function resetBasket()
    {
        $this->basketRepository->removeCouponCode();
        $basketItems = $this->getBasketItemsRaw();

        foreach ($basketItems as $basketItem) {
            if (!in_array($basketItem->itemType, [BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT, BasketItem::BASKET_ITEM_TYPE_VARIATION_ORDER_PROPERTY])) {
                // dont fire events at this place
                $this->basketItemRepository->removeBasketItem($basketItem->id, false);
            }
        }

        $contactId = $this->contactRepository->getContactId();
        if ($contactId > 0) {
            /*  if a regular contact created the order fire now the ignored event. */
            /** @var Dispatcher $dispatcher */
            $dispatcher = pluginApp(Dispatcher::class);
            $dispatcher->fire(pluginApp(AfterBasketChanged::class));
        }
    }

    /**
     * Delete basket for current session
     */
    public function deleteBasket()
    {
        $this->basketRepository->deleteBasket();
    }

    /**
     * Set the billing address id
     *
     * @param int $billingAddressId
     */
    public function setBillingAddressId(int $billingAddressId)
    {
        $this->checkout->setCustomerInvoiceAddressId($billingAddressId);
    }

    /**
     * Return the billing address id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->checkout->getCustomerInvoiceAddressId();
    }

    /**
     * Set the delivery address id
     *
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId(int $deliveryAddressId)
    {
        $this->checkout->setCustomerShippingAddressId($deliveryAddressId);
    }

    /**
     * Return the delivery address id
     *
     * @return int
     */
    public function getDeliveryAddressId()
    {
        return $this->checkout->getCustomerShippingAddressId();
    }

    /**
     * Get the maximum vat value in basket.
     *
     * @return float
     */
    public function getMaxVatValue()
    {
        $maxVatValue = -1;

        foreach ($this->getBasketItemsRaw() as $item) {
            $maxVatValue = max($maxVatValue, $item->vat);
        }

        if ($maxVatValue == -1) {
            if (count($vatRates = $this->vatService->getVat()->vatRates)
                && isset($vatRates[0])) {
                $vatRate = $vatRates[0];
                if ($vatRate instanceof VatRate) {
                    $maxVatValue = $vatRate->vatRate;
                }
            }
        }

        return $maxVatValue;
    }

    private function hasTexts($basketItemData)
    {
        return is_array($basketItemData['texts'])
            && count($basketItemData['texts'])
            && (strlen($basketItemData['texts']['name1'])
                || strlen($basketItemData['texts']['name2'])
                || !strlen($basketItemData['texts']['name3'])
            );
    }

    /**
     * Load the variation data for the basket item
     *
     * @param BasketItem[] $basketItems
     * @param array $basketItemData
     * @param boolean $sortOrderItems
     *
     * @return array
     */
    private function addVariationData($basketItems, $basketItemData, $sortOrderItems = false): array
    {

        $order = $this->sessionStorageRepository->getOrder();
        $isNet = false;
        if (!is_null($order)) {
            $isNet = $order->isNet;
        }
        $showNetPrice = $this->contactRepository->showNetPrices();
        $result = [];
        foreach ($basketItems as $basketItem) {
            if ($isNet || $showNetPrice) {
                $basketItem->price = round($basketItem->price * 100 / (100.0 + $basketItem->vat), 2);
            }

            //load relation, do not remove
            $temp = $basketItem->basketItemOrderParams;

            $arr = $basketItem->toArray();

            if (array_key_exists($basketItem->variationId, $basketItemData)) {
                $arr["variation"] = $basketItemData[$basketItem->variationId];
            } else {
                $arr["variation"] = null;
            }


            if ($sortOrderItems && array_key_exists($basketItem->variationId, $basketItemData)) {
                $arr['basketItemOrderParams'] = $this->getSortedBasketItemOrderParams($arr);
            }

            array_push(
                $result,
                $arr
            );
        }
        return $result;
    }

    /**
     * Add the given data to the basket
     * @param object $data
     * @return array
     */
    private function addDataToBasket($data)
    {
        if (isset($data['basketItemOrderParams'])
            && is_array($data['basketItemOrderParams'])
            && !isset($data['totalOrderParamsMarkup'])) {
            list($data['basketItemOrderParams'], $data['totalOrderParamsMarkup']) = $this->parseBasketItemOrderParams(
                $data['basketItemOrderParams']
            );
        }

        $data['referrerId'] = $this->getBasket()->referrerId;
        $basketItem = $this->findExistingOneByData($data);

        try {
            if ($basketItem instanceof BasketItem) {
                $data['id'] = $basketItem->id;
                $data['quantity'] = (float)$data['quantity'] + $basketItem->quantity;
                $this->basketItemRepository->updateBasketItem($basketItem->id, $data);
            } else {
                $this->basketItemRepository->addBasketItem($data);
            }
        } catch (BasketItemQuantityCheckException $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_addItemQuantityCheckFailed',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            switch ($e->getCode()) {
                case BasketItemQuantityCheckException::DID_REACH_MAXIMUM_QUANTITY_FOR_ITEM:
                    $code = 112;
                    break;
                case BasketItemQuantityCheckException::DID_REACH_MAXIMUM_QUANTITY_FOR_VARIATION:
                    $code = 113;
                    break;
                case BasketItemQuantityCheckException::DID_NOT_REACH_MINIMUM_QUANTITY_FOR_VARIATION:
                    $code = 114;
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code];
        } catch (BasketItemCheckException $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_addItemCheckFailed',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            switch ($e->getCode()) {
                case BasketItemCheckException::VARIATION_NOT_FOUND:
                    $code = 110;
                    break;
                case BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATION:
                    $code = 111;
                    $placeholder = ['stock' => $e->getStockNet(), 'variationId' => $e->getVariationId()];
                    break;
                case BasketItemCheckException::ITEM_SET_COMPONENT_MISMATCH:
                    $code = 1;
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code, 'placeholder' => $placeholder];
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->warning(
                'IO::Debug.BasketService_cannotAddItem',
                [
                    'data' => $data,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            );
            return ["code" => $e->getCode()];
        }
        return [];
    }

    /**
     * Parse basket item order params
     * @param array $basketOrderParams
     * @return array
     */
    private function parseBasketItemOrderParams(array $basketOrderParams): array
    {
        $properties = [];

        $totalOrderParamsMarkup = 0;
        foreach ($basketOrderParams as $key => $basketOrderParam) {
            if (strlen($basketOrderParam['property']['value']) > 0 && isset($basketOrderParam['property']['value'])) {
                $properties[$key]['propertyId'] = $basketOrderParam['property']['id'];
                $properties[$key]['type'] = $basketOrderParam['property']['valueType'];
                $properties[$key]['value'] = $basketOrderParam['property']['value'];
                $properties[$key]['name'] = $basketOrderParam['property']['names']['name'];

                if ($basketOrderParam['surcharge'] > 0) {
                    $totalOrderParamsMarkup += $basketOrderParam['surcharge'];
                } elseif ($basketOrderParam['property']['surcharge'] > 0) {
                    $totalOrderParamsMarkup += $basketOrderParam['property']['surcharge'];
                }
            }
        }

        return [$properties, $totalOrderParamsMarkup];
    }

    /**
     * Get the data of the basket items
     * @param BasketItem[] $basketItems
     * @param string $language The language in format ISO-639-1
     * @return array
     * @throws \Exception
     */
    private function getBasketItemData($basketItems = array(), string $language = ''): array
    {
        if (count($basketItems) <= 0) {
            return array();
        }

        $basketItemVariationIds = [];
        $basketVariationQuantities = [];

        foreach ($basketItems as $basketItem) {
            array_push($basketItemVariationIds, $basketItem->variationId);
            if (!isset($basketVariationQuantities[$basketItem->variationId])) {
                $basketVariationQuantities[$basketItem->variationId] = 0;
            }
            $basketVariationQuantities[$basketItem->variationId] += $basketItem->quantity;
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        $items = $itemSearchService->getResults(
            BasketItems::getSearchFactory(
                [
                    'variationIds' => $basketItemVariationIds,
                    'quantities' => $basketVariationQuantities,
                    'language' => $language
                ]
            )
        );

        $result = [];
        if (isset($items['documents']) && is_array($items['documents'])) {
            foreach ($items['documents'] as $item) {
                $variationId = $item['data']['variation']['id'];
                $result[$variationId] = $item;
                $result[$variationId]['data']['unit']['htmlUnit'] = UnitUtils::getHTML4Unit(
                    $result[$variationId]['data']['unit']['unitOfMeasurement']
                );
            }
        }

        return $result;
    }

    /**
     * Get the data of the basket items
     * @param BasketItem[] $basketItems
     * @return array
     * @throws \Throwable
     */
    private function getOrderItemData($basketItems = array()): array
    {
        if (count($basketItems) <= 0) {
            return array();
        }

        $variationRepository = pluginApp(VariationRepositoryContract::class);
        /**
         * @var VariationRepositoryContract $variationRepository
         */

        $variationDescriptionRepository = pluginApp(VariationDescriptionRepositoryContract::class);
        /**
         * @var VariationDescriptionRepositoryContract $variationDescriptionRepository
         */

        $lang = Utils::getLang();

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $result = [];
        foreach ($basketItems as $basketItem) {
            /**
             * @var Variation $variation
             */
            $variation = $variationRepository->findById($basketItem->variationId);

            /**
             * @var VariationDescription $texts
             */
            $texts = $authHelper->processUnguarded(
                function () use ($variationDescriptionRepository, $basketItem, $lang) {
                    return $variationDescriptionRepository->find($basketItem->variationId, $lang);
                }
            );

            $result[$basketItem->variationId]['data']['variation']['name'] = $variation->name ?? '';
            $result[$basketItem->variationId]['data']['texts']['name1'] = $texts->name ?? '';
            $result[$basketItem->variationId]['data']['texts']['name2'] = $texts->name2 ?? '';
            $result[$basketItem->variationId]['data']['texts']['name3'] = $texts->name3 ?? '';
            $result[$basketItem->variationId]['data']['variation']['vatId'] = $variation->vatId ?? $variation->parent->vatId;
            $result[$basketItem->variationId]['data']['properties'] = $variation->variationProperties->toArray();
            $result[$basketItem->variationId]['data']['basketItemOrderParams'] = $basketItem->basketItemOrderParams;
        }

        return $result;
    }

    private function removeInactiveBasketItems($basketItems = [])
    {
        $items = [];
        foreach ($basketItems as $basketItem) {
            if (is_null($basketItem['variation'])) {
                $this->basketItemRepository->removeBasketItem($basketItem['id'], false);
            } else {
                $items[] = $basketItem;
            }
        }

        if (count($items) != count($basketItems)) {
            /** @var Dispatcher $pluginEventDispatcher */
            $pluginEventDispatcher = pluginApp(Dispatcher::class);
            $pluginEventDispatcher->fire(pluginApp(AfterBasketChanged::class), []);
        }

        return $items;
    }

    private function getSortedBasketItemOrderParams($basketItem): array
    {
        $newParams = [];
        if (!array_key_exists('basketItemOrderParams', $basketItem)) {
            return [];
        }

        foreach ($basketItem['basketItemOrderParams'] as $param) {
            $propertyId = (int)$param['propertyId'];

            foreach ($basketItem['variation']['data']['properties'] as $property) {
                if ($property['property']['id'] === $propertyId) {
                    $newParam = $param;
                    $newParam['position'] = $property['property']['position'];
                    $newParams[] = $newParam;
                }
            }
        }

        usort(
            $newParams,
            function ($documentA, $documentB) {
                return $documentA['position'] - $documentB['position'];
            }
        );

        return $newParams;
    }

    /**
     * @return BasketItem[]
     */
    private function getBasketItemsRaw()
    {
        if (!is_array($this->basketItems)) {
            $this->basketItems = $this->basketItemRepository->all();
        }

        return $this->basketItems;
    }
    private function filterVariationOrderProperties(array $basketItems)
    {
        $variationProperties = [];
        // remove variation order properties from basket items
        $basketItems = array_filter(
            $basketItems,
            function ($basketItem) use (&$variationProperties) {
                if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_VARIATION_ORDER_PROPERTY) {
                    $bundleRowId = $basketItem['itemBundleRowId'];
                    $variationProperties[$bundleRowId] = $variationProperties[$bundleRowId] ?? [];
                    $variationProperties[$bundleRowId][] = $basketItem;
                    return false;
                }
                return true;
            }
        );
        foreach ($basketItems as &$basketItem) {
            if (isset($variationProperties[$basketItem['id']])) {
                $basketItem['basketItemOrderParams'] = $basketItem['basketItemOrderParams'] ?? [];
                foreach ($variationProperties[$basketItem['id']] as $variationProperty) {
                    $property = PropertyHelper::getPropertyById($variationProperty['basketItemOrderParams'][0]['propertyId']);
                    $isAdditionalCost = false;
                    $hasTax = false;
                    foreach ($property['options'] as $option) {
                        if ($option['type'] === 'vatId' && ($option['value'] !== 'none' || $option['value'] !== null)) {
                            $hasTax = true;
                        }
                        if ($option['value'] === 'displayAsAdditionalCosts') {
                            $isAdditionalCost = true;
                        }
                    }
                    if (!$isAdditionalCost && $hasTax) {
                        $basketItem['price'] += $variationProperty['price'];
                    }
                    $basketItem['attributeTotalMarkup'] += $variationProperty['price'];
                    $variationProperty['basketItemOrderParams'][0]['price'] = $variationProperty['price'];
                    $variationProperty['basketItemOrderParams'][0]['basketItemId'] = $basketItem['id'];
                    // map order params from variation property item to origin basket item
                    // each variation property basket item contains exactly one order param
                    $basketItem['basketItemOrderParams'][] = $variationProperty['basketItemOrderParams'][0];
                }
            }
        }

        // array_filter preserves keys of entries. array_values generates a new array with new keys from 0..n
        return array_values($basketItems);
    }
    private function filterSetItems($basketItems)
    {
        $setComponents = [];

        // remove set components from basket items
        $basketItems = array_filter(
            $basketItems,
            function ($basketItem) use (&$setComponents) {
                if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT) {
                    // store set components to add them to the parent item later
                    $bundleRowId = $basketItem['itemBundleRowId'];
                    $setComponents[$bundleRowId] = $setComponents[$bundleRowId] ?? [];
                    $setComponents[$bundleRowId][] = $basketItem;
                    return false;
                }
                return true;
            }
        );

        // append set components
        foreach ($basketItems as &$basketItem) {
            if ($basketItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET && array_key_exists(
                    $basketItem['id'],
                    $setComponents
                )) {
                $basketItem['setComponents'] = $setComponents[$basketItem['id']];
            }
        }

        // array_filter preserves keys of entries. array_values generates a new array with new keys from 0..n
        return array_values($basketItems);
    }

    private function getSetComponents($basketItemId, $appendVariation = true)
    {
        if ($appendVariation) {
            $basketItems = $this->getBasketItems() ;
        } else {
            $temp = $this->getBasketItemsRaw();

            $basketItems = [];
            foreach($temp as $basketItem) {
                $basketItems[] = $basketItem;
            }
        }

        return array_filter(
            $basketItems,
            function ($bItem) use ($basketItemId) {
                return $bItem['itemBundleRowId'] == $basketItemId && $bItem['itemType'] === BasketItem::BASKET_ITEM_TYPE_ITEM_SET_COMPONENT;
            }
        );
    }

    private function reduceBasketItem($basketItem)
    {
        return [
            'id' => $basketItem['id'],
            'quantity' => $basketItem['quantity'],
            'price' => $basketItem['price'],
            'itemId' => $basketItem['itemId'],
            'variation' => $basketItem['variation'],
            'variationId' => $basketItem['variationId'],
            'basketItemOrderParams' => $basketItem['basketItemOrderParams'] ?? null,
            'inputLength' => $basketItem['inputLength'] ?? 0,
            'inputWidth' => $basketItem['inputWidth'] ?? 0,
            'setComponents' => $basketItem['setComponents'] ?? [],
            'itemType' => $basketItem['itemType']
        ];
    }
}
