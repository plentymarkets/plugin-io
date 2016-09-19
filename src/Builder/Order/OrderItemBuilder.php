<?php //strict

namespace LayoutCore\Builder\Order;

use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Item\DataLayer\Models\Record;
use LayoutCore\Services\CheckoutService;

class OrderItemBuilder
{
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;

	public function __construct(CheckoutService $checkoutService)
	{
		$this->checkoutService = $checkoutService;
	}

	public function fromBasket(Basket $basket, array $items):array
	{
		$orderItems = [];
		foreach($basket->basketItems as $basketItem)
		{
			$basketItemName = $items[$basketItem->variationId]->itemDescription->name1;
			array_push($orderItems, $this->basketItemToOrderItem($basketItem, $basketItemName));
		}

		return $orderItems;
	}

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
			],
			"options"           => [] // TODO
		];
	}

}
