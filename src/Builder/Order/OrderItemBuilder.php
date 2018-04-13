<?php //strict

namespace IO\Builder\Order;

use IO\Extensions\Filters\ItemNameFilter;
use IO\Services\SessionStorageService;
use Plenty\Modules\Basket\Models\Basket;
use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Modules\Frontend\Services\VatService;

/**
 * Class OrderItemBuilder
 * @package IO\Builder\Order
 */
class OrderItemBuilder
{
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;

    /**
     * @var VatService
     */
	private $vatService;

	/** @var ItemNameFilter */
	private $itemNameFilter;

	/**
	 * OrderItemBuilder constructor.
	 * @param CheckoutService $checkoutService
	 */
	public function __construct(CheckoutService $checkoutService, VatService $vatService, ItemNameFilter $itemNameFilter)
	{
		$this->checkoutService = $checkoutService;
		$this->vatService = $vatService;
		$this->itemNameFilter = $itemNameFilter;
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

        foreach($items as $item)
		{
            if($maxVatRate < $item['vat'])
            {
                $maxVatRate = $item['vat'];
            }

			array_push($orderItems, $this->basketItemToOrderItem($item, $basket->basketRebate));
		}


		// add shipping costs
        $shippingCosts = [
            "typeId"        => OrderItemType::SHIPPING_COSTS,
            "referrerId"    => $basket->basketItems->first()->referrerId,
            "quantity"      => 1,
            "orderItemName" => "shipping costs",
            "countryVatId"  => $this->vatService->getCountryVatId(),
            "vatRate"       => $maxVatRate,
            "amounts"       => [
                [
                    "currency"              => $this->checkoutService->getCurrency(),
                    "priceOriginalGross"    => $basket->shippingAmount
                ]
            ]
        ];
        array_push($orderItems, $shippingCosts);

		$paymentFee = pluginApp(FrontendPaymentMethodRepositoryContract::class)
			->getPaymentMethodFeeById($this->checkoutService->getMethodOfPaymentId());

		$paymentSurcharge = [
			"typeId"        => OrderItemType::PAYMENT_SURCHARGE,
			"referrerId"    => $basket->basketItems->first()->referrerId,
			"quantity"      => 1,
			"orderItemName" => "payment surcharge",
			"countryVatId"  => $this->vatService->getCountryVatId(),
			"vatRate"       => $maxVatRate,
			"amounts"       => [
				[
					"currency"           => $this->checkoutService->getCurrency(),
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
        
		$priceOriginal = $basketItem['variation']['data']['prices']['default']['data']['basePrice'];

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

		return [
			"typeId"            => OrderItemType::VARIATION,
			"referrerId"        => $basketItem['referrerId'],
			"itemVariationId"   => $basketItem['variationId'],
			"quantity"          => $basketItem['quantity'],
			"orderItemName"     => $this->itemNameFilter->itemName( $basketItem['variation']['data'] ),
			"shippingProfileId" => $basketItem['shippingProfileId'],
			"countryVatId"      => $this->vatService->getCountryVatId(),
			"vatRate"           => $basketItem['vat'],
			//"vatField"			=> $basketItem->vatField,// TODO
            "orderProperties"   => $basketItemProperties,
			"amounts"           => [
				[
					"currency"              => $this->checkoutService->getCurrency(),
					"priceOriginalGross"    => $priceOriginal,
                    "surcharge"             => $attributeTotalMarkup,
					"discount"	            => $rebate,
					"isPercentage"          => true
				]
			]
		];
	}

}
