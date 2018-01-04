<?php //strict

namespace IO\Services;

use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Extensions\Filters\NumberFormatFilter;
use Plenty\Modules\Frontend\Services\VatService;

/**
 * Class BasketService
 * @package IO\Services
 */
class BasketService
{
    /**
     * @var BasketItemRepositoryContract
     */
    private $basketItemRepository;

    /**
     * @var Checkout
     */
    private $checkout;

    private $template = '';
    /**
     * @var VatService
     */
    private $vatService;

    /**
     * @var SessionStorageService
     */
    private $sessionStorage;

    private $basketItems;

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     * @param VatService $vatService
     */
    public function __construct(BasketItemRepositoryContract $basketItemRepository, Checkout $checkout, VatService $vatService, SessionStorageService $sessionStorage)
    {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout             = $checkout;
        $this->vatService           = $vatService;
        $this->sessionStorage       = $sessionStorage;
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    public function getBasketForTemplate(): array
    {
        $basket = $this->getBasket()->toArray();

        $basket["itemQuantity"] = $this->getBasketQuantity();
        $basket["totalVats"] = $this->getTotalVats();


        if ($this->sessionStorage->getCustomer()->showNetPrice) {
            $basket["itemSum"]        = $basket["itemSumNet"];
            $basket["basketAmount"]   = $basket["basketAmountNet"];
            $basket["shippingAmount"] = $basket["shippingAmountNet"];
        }

        return $basket;
    }

    /**
     * Return the basket as an array
     * @return Basket
     */
    public function getBasket(): Basket
    {
        $basket = pluginApp(BasketRepositoryContract::class)->load();
        $basket->currency = pluginApp(CheckoutService::class)->getCurrency();
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

        foreach ($this->getBasketItems() as $item) {
            if ($item["variationId"] > 0) {
                $itemQuantity += $item["quantity"];
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
        $result = array();

        $basketItems        = $this->getBasketItemsRaw();
        $basketItemData     = $this->getBasketItemData($basketItems);
        $showNetPrice       = $this->sessionStorage->getCustomer()->showNetPrice;

        foreach ($basketItems as $basketItem) {
            if ($showNetPrice) {
                $basketItem->price = round($basketItem->price * 100 / (100.0 + $basketItem->vat), 2);
            }

            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }

        return $result;
    }

    public function getBasketItemsForTemplate(string $template = ''): array
    {
        if (!strlen($template)) {
            $template = $this->template;
        }

        $result = array();

        $numberFormatFilter = pluginApp(NumberFormatFilter::class);

        $basketItems    = $this->getBasketItemsRaw();
        $basketItemData = $this->getBasketItemData($basketItems, $template);
        $showNetPrice   = $this->sessionStorage->getCustomer()->showNetPrice;
        $currency       = $this->getBasket()->currency;

        foreach ($basketItems as $basketItem) {
            if ($showNetPrice) {
                $basePrice = $basketItemData[$basketItem->variationId]['data']['calculatedPrices']['default']->basePrice;
                $basePrice = $basePrice * 100 / (100.0 + $basketItem->vat);

                $basketItemData[$basketItem->variationId]['data']['calculatedPrices']['default']->basePrice     = $basePrice;
                $basketItemData[$basketItem->variationId]['data']['calculatedPrices']['formatted']['basePrice'] = $numberFormatFilter->formatMonetary($basePrice,
                    $currency);

                $priceNet = $basketItem->price * 100 / (100.0 + $basketItem->vat);

                $basketItemData[$basketItem->variationId]['data']['calculatedPrices']['default']->unitPrice = $priceNet;
                $basketItemData[$basketItem->variationId]['data']['calculatedPrices']['default']->price     = $priceNet;
            }
            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }

        return $result;
    }

    /**
     * Get a basket item
     * @param int $basketItemId
     * @return array
     */
    public function getBasketItem(int $basketItemId): array
    {
        $basketItem = $this->basketItemRepository->findOneById($basketItemId);
        if ($basketItem === null) {
            return array();
        }
        $basketItemData = $this->getBasketItemData($basketItem->toArray());
        return $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId]);
    }

    /**
     * Load the variation data for the basket item
     * @param BasketItem $basketItem
     * @param $variationData
     * @return array
     */
    private function addVariationData(BasketItem $basketItem, $variationData): array
    {
        $arr              = $basketItem->toArray();
        $arr["variation"] = $variationData;
        return $arr;
    }

    /**
     * Add an item to the basket or update the basket
     * @param array $data
     * @return array
     */
    public function addBasketItem(array $data): array
    {

        if (isset($data['basketItemOrderParams']) && is_array($data['basketItemOrderParams'])) {
            list($data['basketItemOrderParams'], $data['totalOrderParamsMarkup']) = $this->parseBasketItemOrderParams($data['basketItemOrderParams']);
        }

        $data['referrerId'] = $this->getBasket()->referrerId;
        $basketItem = $this->findExistingOneByData($data);

        try {
            if ($basketItem instanceof BasketItem) {
                $data['id']       = $basketItem->id;
                $data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
                $this->basketItemRepository->updateBasketItem($basketItem->id, $data);
            } else {
                $this->basketItemRepository->addBasketItem($data);
            }
        } catch (\Exception $e) {
            return ["code" => $e->getCode()];
        }

        return $this->getBasketItemsForTemplate();
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

                $properties[$key]['propertyId'] = $basketOrderParam['property']['names']['propertyId'];
                $properties[$key]['type']       = $basketOrderParam['property']['valueType'];
                $properties[$key]['value']      = $basketOrderParam['property']['value'];
                $properties[$key]['name']       = $basketOrderParam['property']['names']['name'];

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
        $data['id'] = $basketItemId;
        try {
            $this->basketItemRepository->updateBasketItem($basketItemId, $data);
        } catch (\Exception $e) {
            return ["code" => $e->getCode()];
        }
        return $this->getBasketItemsForTemplate();
    }

    /**
     * Delete an item from the basket
     * @param int $basketItemId
     * @return array
     */
    public function deleteBasketItem(int $basketItemId): array
    {
        $this->basketItemRepository->removeBasketItem($basketItemId);
        return $this->getBasketItemsForTemplate();
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
     * @param string $template
     * @return array
     */
    private function getBasketItemData($basketItems = array(), string $template = ''): array
    {
        if (!strlen($template)) {
            $template = $this->template;
        }

        if (count($basketItems) <= 0) {
            return array();
        }
        $numberFormatFilter = pluginApp(NumberFormatFilter::class);
        $currency           = $this->getBasket()->currency;

        $basketItemVariationIds    = [];
        $basketVariationQuantities = [];
        $orderProperties           = [];

        foreach ($basketItems as $basketItem) {
            array_push($basketItemVariationIds, $basketItem->variationId);
            $basketVariationQuantities[$basketItem->variationId] = $basketItem->quantity;
            $orderProperties[$basketItem->variationId]           = $basketItem->basketItemOrderParams;
        }

        /** @var TwigLoaderPresets $loaderPresets */
        $loaderPresets = pluginApp(TwigLoaderPresets::class);
        $presets = $loaderPresets->getGlobals();
        $items = pluginApp(ItemLoaderService::class)
            ->loadForTemplate(
                $template,
                $presets['itemLoaderPresets']['basketItems'],
                [
                    'variationIds' => $basketItemVariationIds,
                    'basketVariationQuantities' => $basketVariationQuantities,
                    'items' => count($basketItemVariationIds), 'page' => 1
                ]);

        $result = array();
        foreach ($items['documents'] as $item) {
            $variationId                                     = $item['data']['variation']['id'];
            $result[$variationId]                            = $item;
            $result[$variationId]['data']['orderProperties'] = $orderProperties[$variationId];
        }

        foreach ($basketItems as $basketItem) {
            $priceNet = $basketItem->price * 100 / (100.0 + $basketItem->vat);
            $price = $basketItem->price;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->unitPrice    = $price;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->price        = $price;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->unitPriceNet = $priceNet;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->priceNet     = $priceNet;

            $result[$basketItem->variationId]['data']['calculatedPrices']['formatted']['defaultPrice']     = $numberFormatFilter->formatMonetary($price, $currency);
            $result[$basketItem->variationId]['data']['calculatedPrices']['formatted']['defaultUnitPrice'] = $numberFormatFilter->formatMonetary($price, $currency);
        }

        return $result;
    }

    public function resetBasket()
    {
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
}
