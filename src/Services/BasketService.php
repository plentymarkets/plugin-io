<?php //strict

namespace IO\Services;

use IO\Services\ItemSearch\SearchPresets\BasketItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Extensions\Filters\NumberFormatFilter;
use Plenty\Modules\Frontend\Services\VatService;
use IO\Services\NotificationService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;

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
     * @var BasketRepositoryContract
     */
    private $basketRepository;

    /**
     * @var CouponCampaignRepositoryContract
     */
    private $couponCampaignRepository;

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
    public function __construct(BasketItemRepositoryContract $basketItemRepository, Checkout $checkout, VatService $vatService, SessionStorageService $sessionStorage, CouponCampaignRepositoryContract $couponCampaignRepository, BasketRepositoryContract $basketRepository)
    {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout             = $checkout;
        $this->vatService           = $vatService;
        $this->sessionStorage       = $sessionStorage;
        $this->couponCampaignRepository = $couponCampaignRepository;
        $this->basketRepository = $basketRepository;

    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    public function getBasketForTemplate(): array
    {
        $basket = $this->getBasket()->toArray();

        $basket["itemQuantity"] = $this->getBasketQuantity();

        if ( $basket["itemQuantity"] > 0 )
        {
            $basket["totalVats"] = $this->getTotalVats();
        }
        else
        {
            $basket["totalVats"] = [];
        }


        if (count($basket['totalVats']) <= 0)
        {
            $basket["itemSum"]        = $basket["itemSumNet"];
            $basket["basketAmount"]   = $basket["basketAmountNet"];
            $basket["shippingAmount"] = $basket["shippingAmountNet"];
        }

        $basket = $this->checkCoupon($basket);

        return $basket;
    }

    /**
     * @param $basket
     * @return array
     */
    public function checkCoupon($basket): array
    {
        if(isset($basket['couponCode']) && strlen($basket['couponCode']) > 0)
        {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket['couponCode']);

            if($campaign instanceof CouponCampaign)
            {
                if($campaign->couponType == CouponCampaign::COUPON_TYPE_SALES)
                {
                    $basket['openAmount']       = $basket['basketAmount'];
                    $basket["basketAmount"]     -= $basket['couponDiscount'];
                    $basket["basketAmountNet"]  -= $basket['couponDiscount'];

                }
                $basket['couponCampaignType'] = $campaign->couponType;
            }
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

        foreach ($this->getBasketItemsRaw() as $item) {
            if ( $item->variationId > 0 )
            {
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

        $basketItems    = $this->getBasketItemsRaw();
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
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);

        if($webstoreConfigService->getWebstoreConfig()->dontSplitItemBundle === 0)
        {
            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp( ItemSearchService::class );

            /** @var VariationSearchFactory $searchFactory */
            $searchFactory = pluginApp( VariationSearchFactory::class );

            $item = $itemSearchService->getResult(
                $searchFactory
                    ->hasVariationId( $data['variationId'] )
                    ->withBundleComponents()
                    ->withResultFields([
                        'variation.bundleType'
                    ])
            );

            if($item['documents']['0']['data']['variation']['bundleType'] === 'bundle')
            {
                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);

                $notificationService->warn('Item bundle split', 5);

                foreach ($item['documents']['0']['data']['bundleComponents'] as $bundleComponent)
                {
                    $basketData = [];

                    $basketData['variationId']  = $bundleComponent['data']['variation']['id'];
                    $basketData['quantity']     = $bundleComponent['quantity'];
                    $basketData['template']     = $data['template'];

                    $this->addDataToBasket($basketData);
                }
            }
        }
        else
        {
            $error = $this->addDataToBasket($data);
            if(array_key_exists("code", $error))
            {
                return $error;
            }
        }

        return $this->getBasketItemsForTemplate();
    }

    /**
     * Add the given data to the basket
     * @param $data
     * @return array
     */
    private function addDataToBasket($data)
    {
        if (isset($data['basketItemOrderParams']) && is_array($data['basketItemOrderParams'])) {
            list($data['basketItemOrderParams'], $data['totalOrderParamsMarkup']) = $this->parseBasketItemOrderParams($data['basketItemOrderParams']);
        }

        $data['referrerId'] = $this->getBasket()->referrerId;
        $basketItem = $this->findExistingOneByData($data);

        try {
            if ($basketItem instanceof BasketItem) {
                $data['id']       = $basketItem->id;
                $data['quantity'] = (float)$data['quantity'] + $basketItem->quantity;
                $this->basketItemRepository->updateBasketItem($basketItem->id, $data);
            } else {
                $this->basketItemRepository->addBasketItem($data);
            }
        } catch (BasketItemCheckException $e) {
            if ($e->getCode() == BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_ITEM) {
                return ["code" => "6"];
            }
        } catch (\Exception $e) {
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
        $basket = $this->getBasket();
        $basketItem = $this->getBasketItem($basketItemId);

        if(strlen($basket->couponCode) > 0)
        {
            $campaign = $this->couponCampaignRepository->findByCouponCode($basket->couponCode);

            // $basket->basketAmount is basket amount minus coupon value
            // $basket->couponDiscount is negative
            if($campaign instanceof CouponCampaign && $campaign->minOrderValue > (( $basket->basketAmount - $basket->couponDiscount ) - ($basketItem['price'] * $basketItem['quantity'])))
            {
                $this->basketRepository->removeCouponCode();

                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);
                $notificationService->info('CouponValidation',301);
            }
        }

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

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $items = $itemSearchService->getResults(
            BasketItems::getSearchFactory([
                'variationIds'  => $basketItemVariationIds,
                'quantities'    => $basketVariationQuantities
            ])
        );

        $result = array();
        foreach ($items['documents'] as $item) {
            $variationId                                     = $item['data']['variation']['id'];
            $result[$variationId]                            = $item;
            $result[$variationId]['data']['orderProperties'] = $orderProperties[$variationId];
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
}