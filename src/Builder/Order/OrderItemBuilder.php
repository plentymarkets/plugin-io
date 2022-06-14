<?php //strict

namespace IO\Builder\Order;

use IO\Extensions\Filters\ItemNameFilter;
use IO\Services\BasketService;
use IO\Events\Basket\BeforeBasketItemToOrderItem;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Basket\Models\Basket;
use IO\Services\CheckoutService;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Accounting\Vat\Contracts\VatRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\Accounting\Vat\Models\Vat;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class OrderItemBuilder
 * @package IO\Builder\Order
 */
class OrderItemBuilder
{
	/**
	 * @var CheckoutService $checkoutService
	 */
	private $checkoutService;

	/** @var CheckoutRepositoryContract $checkoutRepository */
	private $checkoutRepository;

    /**
     * @var VatService $vatService
     */
	private $vatService;

	/** @var ItemNameFilter $itemNameFilter */
	private $itemNameFilter;

    /**
     * @var VatRepositoryContract $vatRepository
     */
    private $vatRepository;

    /**
     * @var WebstoreRepositoryContract $webstoreRepository
     */
    private $webstoreRepository;

    /**
     * @var ContactRepositoryContract $contactRepository
     */
    private $contactRepository;

    /**
     * OrderItemBuilder constructor.
     *
     * @param CheckoutService $checkoutService
     * @param VatService $vatService
     * @param ItemNameFilter $itemNameFilter
     * @param WebstoreRepositoryContract $webstoreRepository
     * @param VatRepositoryContract $vatRepository
     * @param ContactRepositoryContract $contactRepository
     * @param CheckoutRepositoryContract $checkoutRepository
     */
	public function __construct(
	    CheckoutService $checkoutService,
        VatService $vatService,
        ItemNameFilter $itemNameFilter,
        WebstoreRepositoryContract $webstoreRepository,
        VatRepositoryContract $vatRepository,
        ContactRepositoryContract $contactRepository,
        CheckoutRepositoryContract $checkoutRepository)
	{
		$this->checkoutService = $checkoutService;
		$this->vatService = $vatService;
        $this->webstoreRepository = $webstoreRepository;
        $this->vatRepository = $vatRepository;
        $this->itemNameFilter = $itemNameFilter;
        $this->contactRepository = $contactRepository;
        $this->checkoutRepository = $checkoutRepository;
	}

	/**
	 * Add a basket item to the order
	 * @param Basket $basket
	 * @param array $items
	 * @return array
	 */
	public function fromBasket(Basket $basket, array $items):array
	{
		$orderItems      = [];
        $maxVatRate      = 0;

        $itemsWithCouponRestriction = [];
        $itemsWithoutStock          = [];

        $taxFreeItems = [];
        
        foreach($items as $item)
		{
            if($maxVatRate < $item['vat'])
            {
                $maxVatRate = $item['vat'];
            }

            try
            {
                array_push($orderItems, $this->basketItemToOrderItem($item, $basket->basketRebate));
    
                //convert tax free properties to order items
                if(is_array($item['variation']['data']['properties']) && count($item['variation']['data']['properties']))
                {
                    foreach($item['variation']['data']['properties'] as $property)
                    {
                        if($property['property']['isShownAsAdditionalCosts'] && !$property['property']['isOderProperty'])
                        {
                            if(array_key_exists($property['propertyId'], $taxFreeItems))
                            {
                                $taxFreeItems[$property['propertyId']]['quantity'] += $item['quantity'];
                            }
                            else
                            {
                                $taxFreeItem = [
                                    "itemId"          => -2,
                                    "itemVariationId" => -2,
                                    "typeId"          => OrderItemType::DEPOSIT,
                                    "referrerId"      => $basket->basketItems->first()->referrerId,
                                    "quantity"        => $item['quantity'],
                                    "orderItemName"   => $property['property']['backendName'] ?? 'tax free item',
                                    "amounts"         => [
                                        [
                                            "currency"           => $this->checkoutRepository->getCurrency(),
                                            "priceOriginalGross" => $property['property']['surcharge']
                                        ]
                                    ]
                                ];
                                
                                $taxFreeItems[$property['propertyId']] = $taxFreeItem;
                            }
                        }
                    }
                }
            }
			catch(BasketItemCheckException $exception)
            {
                if ($exception->getCode() === BasketItemCheckException::COUPON_REQUIRED)
                {
                    $itemsWithCouponRestriction[] = $item;
                }
                else
                {
                    $itemsWithoutStock[] = [
                        'item' => $item,
                        'stockNet' => $exception->getStockNet()
                    ];
                }
            }
		}

		if(count($itemsWithoutStock))
        {
            /** @var BasketService $basketService */
            $basketService = pluginApp(BasketService::class);

            foreach($itemsWithoutStock as $itemWithoutStock)
            {

                $filteredWithoutStock = array_filter($items, function($filterItem) use ($itemWithoutStock) {
                    return $filterItem['id'] == $itemWithoutStock['item']['id'];
                });
                $updatedItem = array_shift($filteredWithoutStock);

                $quantity = $itemWithoutStock['stockNet'];

                if($quantity <= 0 && (int)$updatedItem['id'] > 0)
                {
                    $basketService->deleteBasketItem($updatedItem['id']);
                }
                elseif((int)$updatedItem['id'] > 0)
                {
                    $updatedItem['quantity'] = $quantity;
                    $basketService->updateBasketItem($updatedItem['id'], $updatedItem);
                }
            }

            throw pluginApp(BasketItemCheckException::class, [BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_ITEM]);
        }

		if(count($itemsWithCouponRestriction))
        {
            throw pluginApp(BasketItemCheckException::class, [BasketItemCheckException::COUPON_REQUIRED]);
        }
        
		// add tax free items
        if(count($taxFreeItems))
        {
            foreach($taxFreeItems as $taxFreeOrderItem)
            {
                array_push($orderItems, $taxFreeOrderItem);
            }
        }
        
		$shippingAmount = $basket->shippingAmount;

		// add shipping costs
        $shippingCosts = [
            "typeId"        => OrderItemType::SHIPPING_COSTS,
            "referrerId"    => $basket->basketItems->first()->referrerId,
            "quantity"      => 1,
            "orderItemName" => "shipping costs",
            "countryVatId"  => $this->vatService->getCountryVatId(),
            'vatField'      => $this->getVatField($this->vatService->getVat(), $maxVatRate),
            "amounts"       => [
                [
                    "currency"              => $this->checkoutRepository->getCurrency(),
                    "priceOriginalGross"    => $shippingAmount
                ]
            ]
        ];
        array_push($orderItems, $shippingCosts);

        /** @var FrontendPaymentMethodRepositoryContract $paymentMethodRepo */
        $paymentMethodRepo = pluginApp(FrontendPaymentMethodRepositoryContract::class);
		$paymentFee = $paymentMethodRepo->getPaymentMethodFeeById($this->checkoutService->getMethodOfPaymentId());

		$paymentSurcharge = [
			"typeId"        => OrderItemType::PAYMENT_SURCHARGE,
			"referrerId"    => $basket->basketItems->first()->referrerId,
			"quantity"      => 1,
			"orderItemName" => "payment surcharge",
			"countryVatId"  => $this->vatService->getCountryVatId(),
            'vatField'      => $this->getVatField($this->vatService->getVat(), $maxVatRate),
            "amounts"       => [
				[
					"currency"           => $this->checkoutRepository->getCurrency(),
					"priceOriginalGross" => $paymentFee
				]
			]
		];
		array_push($orderItems, $paymentSurcharge);

		return $orderItems;
	}

	/**
	 * Add a basket item to the order
	 * @param array $basketItem
	 * @return array
	 */
	private function basketItemToOrderItem(array $basketItem, $basketDiscount):array
	{
        /** @var BasketItem $checkStockBasketItem */
        $checkStockBasketItem = pluginApp(BasketItem::class);
        $checkStockBasketItem->variationId = $basketItem['variationId'];
        $checkStockBasketItem->itemId      = $basketItem['itemId'];
        $checkStockBasketItem->orderRowId  = $basketItem['orderRowId'];
        $checkStockBasketItem->quantity    = $basketItem['quantity'];
        $checkStockBasketItem->id          = $basketItem['id'];

        /** @var Dispatcher $eventDispatcher */
        $eventDispatcher = pluginApp(Dispatcher::class);
        $eventDispatcher->fire(pluginApp(BeforeBasketItemToOrderItem::class, [$checkStockBasketItem]));

        $basketItemProperties = [];
        if(count($basketItem['basketItemOrderParams']))
        {
            /** @var OrderPropertyFileService $orderPropertyFileService */
            $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);

            foreach($basketItem['basketItemOrderParams'] as $property)
            {
                if($property['type'] == 'file')
                {
                    $file = $orderPropertyFileService->copyBasketFileToOrder($property['value']);
                    $property['value'] = $file;
                }

                $basketItemProperty = [
                    'propertyId' => $property['propertyId'],
                    'value'      => $property['value']
                ];

                $basketItemProperties[] = $basketItemProperty;
            }
        }

        $attributeTotalMarkup = 0;
		if(isset($basketItem['attributeTotalMarkup']))
		{
            $attributeTotalMarkup = $basketItem['attributeTotalMarkup'];
        }

        $rebate = 0;
        if(isset($basketItem['rebate']))
		{
			$rebate = $basketItem['rebate'];
		}
		if((float)$basketDiscount > 0)
        {
            $rebate += $basketDiscount;
        }

        $priceOriginal = $basketItem['price'];
		if ( $this->contactRepository->showNetPrices() )
        {
            $priceOriginal = $basketItem['price'] * (100.0 + $basketItem['vat']) / 100.0;
        }
        $priceOriginal -= $attributeTotalMarkup;

		$properties = [];
		if($basketItem['inputLength'] > 0)
        {
            $properties[] = [
                'typeId' => OrderPropertyType::LENGTH,
                'value' => "{$basketItem['inputLength']}"
            ];
        }
		if($basketItem['inputWidth'] > 0)
        {
            $properties[] = [
                'typeId' => OrderPropertyType::WIDTH,
                'value' => "{$basketItem['inputWidth']}"
            ];
        }

		return [
			"typeId"            => OrderItemType::VARIATION,
			"referrerId"        => $basketItem['referrerId'],
			"itemVariationId"   => $basketItem['variationId'],
			"quantity"          => $basketItem['quantity'],
			"orderItemName"     => $this->itemNameFilter->itemName( $basketItem['variation']['data'] ),
			"shippingProfileId" => $basketItem['shippingProfileId'],
			"countryVatId"      => $this->vatService->getCountryVatId(),
            "vatField"			=> $basketItem['variation']['data']['variation']['vatId'] ?? $this->getVatField($this->vatService->getVat(), $basketItem['vat']),
            "orderProperties"   => $basketItemProperties,
            "properties"        => $properties,
			"amounts"           => [
				[
					"currency"              => $this->checkoutRepository->getCurrency(),
					"priceOriginalGross"    => $priceOriginal,
                    "surcharge"             => $attributeTotalMarkup,
					"discount"	            => $rebate,
					"isPercentage"          => true
				]
			]
		];
	}

    /**
     * Get the vat field for the given vat rate.
     *
     * @param Vat   $vat		The country VAT instance.
     * @param float $vatRate	The vat rate.
     *
     * @return int
     */
    public function getVatField(Vat $vat, $vatRate)
    {
        $vatRateArray = $vat->vatRates;
        switch($vatRate)
        {
            case $vatRateArray[0]->vatRate:
                return 0;
            case $vatRateArray[1]->vatRate:
                return 1;
            case $vatRateArray[2]->vatRate:
                return 2;
            case $vatRateArray[3]->vatRate:
                return 3;
            default:
                if($vat->isStandard)
                {
                    return 0;
                }
        }
        $storeId = $vat->location->clientId;
        $plentyId = (int)$this->webstoreRepository->findById($storeId)->storeIdentifier;
        $standardVat = $this->vatRepository->getStandardVat($plentyId);
        return $this->getVatField($standardVat, $vatRate);
    }
}
