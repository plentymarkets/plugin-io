<?php //strict

namespace IO\Services;

use IO\Services\VdiSearch\SearchPresets\BasketItems;
use IO\Services\VdiSearch\Services\ItemSearchService;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Accounting\Vat\Models\VatRate;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Basket\Exceptions\BasketItemQuantityCheckException;
use Plenty\Modules\Item\VariationCategory\Contracts\VariationCategoryRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use IO\Extensions\Filters\NumberFormatFilter;
use Plenty\Modules\Frontend\Services\VatService;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Constants\LogLevel;

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
     * @var CustomerService
     */
    private $customerService;

    private $basketItems;

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     * @param VatService $vatService
     */
    public function __construct(
        BasketItemRepositoryContract $basketItemRepository,
        Checkout $checkout,
        VatService $vatService,
        CustomerService $customerService,
        CouponCampaignRepositoryContract $couponCampaignRepository,
        BasketRepositoryContract $basketRepository,
        VatInitContract $vatInitService)
    {
        $this->basketItemRepository = $basketItemRepository;
        $this->checkout             = $checkout;
        $this->vatService           = $vatService;
        $this->customerService      = $customerService;
        $this->couponCampaignRepository = $couponCampaignRepository;
        $this->basketRepository = $basketRepository;

        if(!$vatInitService->isInitialized())
        {
            $vat = $this->vatService->getVat();
        }
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
        $showNetPrice       = $this->customerService->showNetPrices();

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
        $showNetPrice   = $this->customerService->showNetPrices();
        
        foreach ($basketItems as $basketItem)
        {
            if($showNetPrice)
            {
                $basketItem->price = round($basketItem->price * 100 / (100.0 + $basketItem->vat), 2);
            }
            
            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }
        
        return $result;
    }
    
    public function checkBasketItemsLang($template = '')
    {
        if (!strlen($template))
        {
            $template = $this->template;
        }
    
        $basketItems    = $this->getBasketItemsRaw();
        $basketItemData = $this->getBasketItemData($basketItems, $template);
        $showWarning = [];
    
        foreach ($basketItems as $basketItem)
        {
            if(!array_key_exists($basketItem->variationId, $basketItemData))
            {
                $this->deleteBasketItem($basketItem->id);
                $showWarning[] = 9;
            }
            elseif (!$this->hasTexts($basketItemData[$basketItem->variationId]['data']))
            {
                $this->deleteBasketItem($basketItem->id);
                $showWarning[] = 10;
            }
        }
    
        if(count($showWarning) > 0)
        {
            $showWarning = array_unique($showWarning);
        
            foreach($showWarning as $warning)
            {
                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);
                $notificationService->warn(LogLevel::WARN, $warning);
            }
        
        }
    }

    private function hasTexts($basketItemData)
    {
        return count($basketItemData['texts']) && (strlen($basketItemData['texts']['name1']) || strlen($basketItemData['texts']['name2']) || !strlen($basketItemData['texts']['name3']));
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
        $basketItemData = $this->getBasketItemData([$basketItem]);
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
            else
            {
                $this->addDataToBasket($data);
            }
        }
        else
        {
            $error = $this->addDataToBasket($data);
            if(is_array($error) && array_key_exists("code", $error))
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
        } catch (BasketItemQuantityCheckException $e) {
             switch($e->getCode()) {
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
            switch($e->getCode()) {
                case BasketItemCheckException::VARIATION_NOT_FOUND:
                    $code = 110;
                    break;
                case BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATION:
                    $code = 111;
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code];
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

                $properties[$key]['propertyId'] = $basketOrderParam['property']['id'];
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
        } catch (BasketItemQuantityCheckException $e) {
             switch($e->getCode()) {
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
            switch($e->getCode()) {
                case BasketItemCheckException::VARIATION_NOT_FOUND:
                    $code = 110;
                    break;
                case BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATION:
                    $code = 111;
                    break;
                default:
                    $code = 0;
            }
            return ["code" => $code];
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
            if($campaign instanceof CouponCampaign)
            {
                /** @var NotificationService $notificationService */
                $notificationService = pluginApp(NotificationService::class);

                if($campaign->minOrderValue > (( $basket->basketAmount - $basket->couponDiscount ) - ($basketItem['price'] * $basketItem['quantity'])))
                {
                    $this->basketRepository->removeCouponCode();
                    $notificationService->info('CouponValidation',301);
                }

                //check if basket item to remove is matching with a coupon campaign and remove coupon if no item with the matching item id of the campaign is left in the basket

                /**
                 * @var VariationCategoryRepositoryContract $variationCategoryRepository
                 */
                $variationCategoryRepository = pluginApp(VariationCategoryRepositoryContract::class);

                $authHelper = pluginApp(AuthHelper::class);
                $variationId = $basketItem['variationId'];
                $categories = $authHelper->processUnguarded( function() use ($variationId, $variationCategoryRepository)
                {
                    return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
                });


                $categoryIds = [];
                $categories->each(function($category) use (&$categoryIds)
                {
                    $categoryIds[] = $category->categoryId;
                });

                $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value', $categoryIds);
                if(count($campaignItems) == 0)
                {
                    $campaignItems = $campaign->references->where('referenceType', 'item')->where('value', $basketItem['itemId']);
                }

                if(count($campaignItems))
                {
                    $matchingBasketItems = $basket->basketItems->where('itemId', $basketItem['itemId']);

                    $basketItems = $basket->basketItems->where('itemId', '!=', $basketItem['itemId']);
                    $noOtherCouponBasketItemsExists = true;

                    $basketItems->each(function($item) use (&$noOtherCouponBasketItemsExists, $campaign, $authHelper, $variationCategoryRepository)
                    {
                        $variationId = $item->variationId;
                        $categories = $authHelper->processUnguarded( function() use ($variationId, $variationCategoryRepository)
                        {
                            return $variationCategoryRepository->findByVariationIdWithInheritance($variationId);
                        });
                        $categoryIds = [];
                        $categories->each(function($category) use (&$categoryIds)
                        {
                            $categoryIds[] = $category->categoryId;
                        });

                        $campaignItems = $campaign->references->where('referenceType', 'category')->whereIn('value', $categoryIds);
                        if(count($campaignItems) == 0)
                        {
                            $campaignItems = $campaign->references->where('referenceType', 'item')->where('value', $item->itemId);
                        }

                        if(count($campaignItems))
                        {
                          $noOtherCouponBasketItemsExists = false;
                          return false;
                        }
                    });

                    if(count($matchingBasketItems) <= 1 && $noOtherCouponBasketItemsExists)
                    {
                        $this->basketRepository->removeCouponCode();
                        $notificationService->info('CouponValidation',302);
                    }
                }
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
            if(!isset($basketVariationQuantities[$basketItem->variationId]))
            {
                $basketVariationQuantities[$basketItem->variationId] = 0;
            }
            $basketVariationQuantities[$basketItem->variationId] += $basketItem->quantity;
            //load relation
            $temp = $basketItem->basketItemOrderParams;
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
            $result[$variationId]['data']['unit']['htmlUnit'] = UnitService::getHTML4Unit($result[$variationId]['data']['unit']['unitOfMeasurement']);
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
