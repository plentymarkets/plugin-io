<?php //strict

namespace IO\Services;

use IO\Services\ItemLoader\Loaders\BasketItems;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Services\ItemService;
use IO\Services\NotificationService;
use IO\Constants\LogLevel;
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
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     * @param VatService $vatService
     */
    public function __construct(BasketItemRepositoryContract $basketItemRepository, Checkout $checkout, VatService $vatService)
    {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout             = $checkout;
        $this->vatService           = $vatService;
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

        return $basket;
    }

    /**
     * Return the basket as an array
     * @return Basket
     */
    public function getBasket(): Basket
    {
        return pluginApp(BasketRepositoryContract::class)->load();
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

        $basketItems    = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData($basketItems);

        foreach ($basketItems as $basketItem) {
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

        $basketItems    = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData($basketItems, $template);

        foreach ($basketItems as $basketItem) {
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
        $this->basketItemRepository->updateBasketItem($basketItemId, $data);
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
     * @param array $basketItems
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

        $basketItemVariationIds    = [];
        $basketVariationQuantities = [];
        $orderPropertries          = [];

        foreach ($basketItems as $basketItem) {
            array_push($basketItemVariationIds, $basketItem->variationId);
            $basketVariationQuantities[$basketItem->variationId] = $basketItem->quantity;
            $orderPropertries[$basketItem->variationId]          = $basketItem->basketItemOrderParams;
        }

        $items = pluginApp(ItemLoaderService::class)
            ->loadForTemplate($template, [BasketItems::class], ['variationIds' => $basketItemVariationIds, 'basketVariationQuantities' => $basketVariationQuantities, 'items' => count($basketItemVariationIds), 'page' => 1]);

        $result = array();
        foreach ($items['documents'] as $item) {
            $variationId                                     = $item['data']['variation']['id'];
            $result[$variationId]                            = $item;
            $result[$variationId]['data']['orderProperties'] = $orderPropertries[$variationId];
        }

        foreach ($basketItems as $basketItem) {
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->unitPrice    = $basketItem->price;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->price        = $basketItem->price;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->unitPriceNet = $basketItem->price / 100 * $basketItem->vat;
            $result[$basketItem->variationId]['data']['calculatedPrices']['default']->priceNet     = $basketItem->price / 100 * $basketItem->vat;

            $numberFormatFilter                                                                            = pluginApp(NumberFormatFilter::class);
            $result[$basketItem->variationId]['data']['calculatedPrices']['formatted']['defaultPrice']     = $numberFormatFilter->formatMonetary($basketItem->price, $result[$basketItem->variationId]['data']['calculatedPrices']['default']->currency);
            $result[$basketItem->variationId]['data']['calculatedPrices']['formatted']['defaultUnitPrice'] = $numberFormatFilter->formatMonetary($basketItem->price, $result[$basketItem->variationId]['data']['calculatedPrices']['default']->currency);
        }

        return $result;
    }

    public function resetBasket()
    {
        $basketItems = $this->basketItemRepository->all();
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
}
