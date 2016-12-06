<?php //strict

namespace IO\Builder\Order;

use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;

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
     * OrderItemBuilder constructor.
     * @param CheckoutService $checkoutService
     */
	public function __construct(CheckoutService $checkoutService)
	{
		$this->checkoutService = $checkoutService;
	}

    /**
     * Add a basket item to the order
     * @param Basket $basket
     * @param array $items
     * @return array
     */
	public function fromBasket(Basket $basket, array $items):array
	{
		$orderItems = [];
		foreach($basket->basketItems as $basketItem)
		{
			//$basketItemName = $items[$basketItem->variationId]->itemDescription->name1;
            $basketItemName = '';
            foreach($items as $item)
            {
                if($basketItem->variationId == $item['variationId'])
                {
                    $basketItemName = $item['variation']->itemDescription->name1;
                }
            }
			array_push($orderItems, $this->basketItemToOrderItem($basketItem, $basketItemName));
		}


		// add shipping costs
        $shippingCosts = [
            "typeId"        => OrderItemType::SHIPPING_COSTS,
            "referrerId"    => $basket->basketItems->first()->referredId,
            "quantity"      => 1,
            "orderItemName" => "shipping costs",
            "countryVatId"  => 1, // TODO get country VAT id
            "vatRate"       => 0, // FIXME get vat rate for shipping costs
            "amounts"       => [
                [
                    "currency"              => $this->checkoutService->getCurrency(),
                    "priceOriginalGross"    => $basket->shippingAmount
                ]
            ]
        ];
        array_push($orderItems, $shippingCosts);

        $paymentFee = pluginApp( FrontendPaymentMethodRepositoryContract::class )
            ->getPaymentMethodFeeById( $this->checkoutService->getMethodOfPaymentId() );

        $paymentSurcharge = [
            "typeId"        => OrderItemType::PAYMENT_SURCHARGE,
            "referrerId"    => $basket->basketItems->first()->referredId,
            "quantity"      => 1,
            "orderItemName" => "payment surcharge",
            "countryVatId"  => 1, // TODO get country VAT id
            "vatRate"       => 0, // FIXME get vat rate for shipping costs
            "amounts"       => [
                [
                    "currency"              => $this->checkoutService->getCurrency(),
                    "priceOriginalGross"    => $paymentFee
                ]
            ]
        ];
        array_push($orderItems, $paymentSurcharge);


		return $orderItems;
	}

    /**
     * Add a basket item to the order
     * @param BasketItem $basketItem
     * @param string $basketItemName
     * @return array
     */
	private function basketItemToOrderItem(BasketItem $basketItem, string $basketItemName):array
	{
		return [
			"typeId"            => OrderItemType::VARIATION,
			"referrerId"        => $basketItem->referrerId,
			"itemVariationId"   => $basketItem->variationId,
			"quantity"          => $basketItem->quantity,
			"orderItemName"     => $basketItemName,
			"shippingProfileId" => $basketItem->shippingProfileId,
			"countryVatId"      => 1, // TODO
			"vatRate"           => $basketItem->vat,
			"amounts"           => [
				[
					"currency"           => $this->checkoutService->getCurrency(),
					"priceOriginalGross" => $basketItem->price
				]
			]
		];
	}

}
