<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Accounting\Contracts\DetermineShopCountryContract;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Basket\Exceptions\BasketItemQuantityCheckException;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\VatService;
use IO\Constants\LogLevel;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;
use Plenty\Modules\Item\VariationDescription\Models\VariationDescription;
use Plenty\Modules\Order\Shipping\Contracts\EUCountryCodesServiceContract;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\Helpers\UnitUtils;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class BasketService
 * @package IO\Services
 */
class BasketService
{
    use Loggable;

    /**
     * @var BasketItemRepositoryContract
     */
    private $basketItemRepository;

    /**
     * @var BasketRepositoryContract
     */
    private $basketRepository;

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var VatService
     */
    private $vatService;

    /**
     * @var ContactRepositoryContract $contactRepository
     */
    private $contactRepository;

    /**
     * @var CouponService $couponService
     */
    private $couponService;

    /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
    private $webstoreConfigurationRepository;

    private $basketItems;
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
     */
    public function __construct(
        BasketItemRepositoryContract $basketItemRepository,
        Checkout $checkout,
        VatService $vatService,
        ContactRepositoryContract $contactRepository,
        BasketRepositoryContract $basketRepository,
        VatInitContract $vatInitService,
        CouponService $couponService,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
    ) {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout = $checkout;
        $this->vatService = $vatService;
        $this->contactRepository = $contactRepository;
        $this->basketRepository = $basketRepository;
        $this->couponService = $couponService;
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;

        if (!$vatInitService->isInitialized()) {
            $vat = $this->vatService->getVat();
        }
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

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


        if (count($basket['totalVats']) <= 0) {
            $basket["itemSum"] = $basket["itemSumNet"];
            $basket["basketAmount"] = $basket["basketAmountNet"];
            $basket["shippingAmount"] = $basket["shippingAmountNet"];
        }

        $basket = $this->couponService->checkCoupon($basket);

        $basket["isExportDelivery"] = $euCountryService->isExportDelivery(
            $basket["shippingCountryId"] ?? $this->webstoreConfigurationRepository->getDefaultShippingCountryId()
        );
        $basket["shopCountryId"] = $determineShopCountry->getCountryId();

        $basket["itemWishListIds"] = $wishListService->getItemWishList();

        return $basket;
    }

    /**
     * Return the basket as an array
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
     * @return array
     */
    public function getTotalVats(): array
    {
        return $this->vatService->getCurrentTotalVats();
    }

    public function getBasketQuantity()
    {
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


    public function getBasketItemsForTemplate(string $template = '', $appendItemData = true): array
    {
        $basketItems = $this->getBasketItemsRaw();
        $basketItemData = $appendItemData ? $this->getBasketItemData($basketItems) : [];
        $basketItems = $this->addVariationData($basketItems, $basketItemData, true);

        $basketItems = array_map(
            function ($basketItem) {
                return $this->reduceBasketItem($basketItem);
            },
            $basketItems
        );

        return $basketItems;
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

    public function checkBasketItemsLang($template = '')
    {
        $basketItems = $this->getBasketItemsRaw();
        $basketItemData = $this->getBasketItemData($basketItems);
        $showWarning = [];

        foreach ($basketItems as $basketItem) {
            if (!array_key_exists($basketItem->variationId, $basketItemData)) {
                $this->deleteBasketItem($basketItem->id);
                $showWarning[] = 9;
            } elseif (!$this->hasTexts($basketItemData[$basketItem->variationId]['data'])) {
                $this->deleteBasketItem($basketItem->id);
                $showWarning[] = 10;
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

    private function hasTexts($basketItemData)
    {
        return count($basketItemData['texts']) && (strlen($basketItemData['texts']['name1']) || strlen(
                    $basketItemData['texts']['name2']
                ) || !strlen($basketItemData['texts']['name3']));
    }

    /**
     * Get a basket item
     * @param int|BasketItem $basketItemId
     * @param bool $appendVariation
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
        $basketItemData = $appendVariation ? $this->getBasketItemData([$basketItem]) : [];
        $basketItems = $this->addVariationData([$basketItem], $basketItemData);
        $basketItem = array_pop($basketItems);

        return $this->reduceBasketItem($basketItem);
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
        $showNetPrice = $this->contactRepository->showNetPrices();
        $result = [];
        foreach ($basketItems as $basketItem) {
            if ($showNetPrice) {
                $basketItem->price = round($basketItem->price * 100 / (100.0 + $basketItem->vat), 2);
            }

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
     * Add an item to the basket or update the basket
     * @param array $data
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
                    $basketData['quantity'] = $bundleComponent['quantity'];
                    $basketData['template'] = $data['template'];

                    $this->addDataToBasket($basketData);
                }
            } else {
                $this->addDataToBasket($data);
            }
        } else {
            $error = $this->addDataToBasket($data);
            if (is_array($error) && array_key_exists("code", $error)) {
                return $error;
            }
        }

        return [];
    }

    /**
     * Add the given data to the basket
     * @param $data
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
     * Update a basket item
     * @param int $basketItemId
     * @param array $data
     * @return array
     */
    public function updateBasketItem(int $basketItemId, array $data): array
    {
        $basket = $this->getBasket();
        $data['id'] = $basketItemId;
        $basketItem = $this->getBasketItem($basketItemId);
        try {
            $this->couponService->validateBasketItemUpdate($basket, $data, $basketItem);
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
     * @param int $basketItemId
     */
    public function deleteBasketItem(int $basketItemId)
    {
        $basket = $this->getBasket();
        $basketItem = $this->getBasketItem($basketItemId);

        // Validate and on fail, remove coupon
        $this->couponService->validateBasketItemDelete($basket, $basketItem);

        $this->basketItemRepository->removeBasketItem($basketItemId);
    }

    /**
     * Check whether the item is already in the basket
     * @param array $data
     * @return null|BasketItem
     */
    public function findExistingOneByData(array $data)
    {
        return $this->basketItemRepository->findExistingOneByData($data);
    }

    /**
     * Get the data of the basket items
     * @param BasketItem[] $basketItems
     * @return array
     */
    private function getBasketItemData($basketItems = array(), string $template = ''): array
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
            //load relation, do not remove
            $temp = $basketItem->basketItemOrderParams;
        }

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        $items = $itemSearchService->getResults(
            BasketItems::getSearchFactory(
                [
                    'variationIds' => $basketItemVariationIds,
                    'quantities' => $basketVariationQuantities
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

    public function resetBasket()
    {
        $this->basketRepository->removeCouponCode();
        $basketItems = $this->getBasketItemsRaw();
        foreach ($basketItems as $basketItem) {
            $this->basketItemRepository->removeBasketItem($basketItem->id);
        }
    }

    /**
     * Set the billing address id
     * @param int $billingAddressId
     */
    public function setBillingAddressId(int $billingAddressId)
    {
        $this->checkout->setCustomerInvoiceAddressId($billingAddressId);
    }

    /**
     * Return the billing address id
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->checkout->getCustomerInvoiceAddressId();
    }

    /**
     * Set the delivery address id
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId(int $deliveryAddressId)
    {
        $this->checkout->setCustomerShippingAddressId($deliveryAddressId);
    }

    /**
     * Return the delivery address id
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

    private function reduceBasketItem($basketItem)
    {
        return [
            "id" => $basketItem["id"],
            "quantity" => $basketItem["quantity"],
            "price" => $basketItem["price"],
            "itemId" => $basketItem["itemId"],
            "variation" => $basketItem["variation"],
            "variationId" => $basketItem["variationId"],
            "basketItemOrderParams" => $basketItem["basketItemOrderParams"] ?? null,
            "inputLength" => $basketItem["inputLength"] ?? 0,
            "inputWidth" => $basketItem["inputWidth"] ?? 0
        ];
    }
}
