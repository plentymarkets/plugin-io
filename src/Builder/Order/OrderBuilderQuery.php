<?php
namespace LayoutCore\Builder\Order;
use LayoutCore\Services\BasketService;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Application;

/**
 * Class OrderBuilderQuery
 * @package LayoutCore\Builder\Order
 */
class OrderBuilderQuery
{
	/**
	 * @var array
	 */
	private $order;

	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var BasketService
	 */
	private $basketService;

	public function __construct(Application $app, BasketService $basketService, int $type, int $plentyId)
	{
		$this->app           = $app;
		$this->basketService = $basketService;

		$this->order             = [];
		$this->order["typeId"]   = $type;
		$this->order["plentyId"] = $plentyId;
	}

	public function done():array
	{
		return $this->order;
	}

	public function fromBasket($basket = null):OrderBuilderQuery
	{
		if($basket === null)
		{
			$basket = $this->basketService->getBasket()["basket"];
			if(!$basket instanceof Basket)
			{
				throw new \Exception("Error while reading basket");
			}
		}

		// add basket items to order
		$orderItemBuilder = $this->app->make(OrderItemBuilder::class);
		if(!$orderItemBuilder instanceof OrderItemBuilder)
		{
			throw new \Exception("Error while instantiating OrderItemBuilder.");
		}

		$items = $this->basketService->getBasketItems()["items"];

		if(!is_array($items) instanceof OrderItemBuilder)
		{
			throw new \Exception("Error while reading item data from basket");
		}

		$this->withOrderItems(
			$orderItemBuilder->fromBasket($basket, $items)
		);

		return $this;
	}

	public function withStatus(float $status):OrderBuilderQuery
	{
		$this->order["statusId"] = $status;
		return $this;
	}

	public function withOwner(int $ownerId):OrderBuilderQuery
	{
		$this->order["ownerId"] = $ownerId;
		return $this;
	}

	public function withOrderItem(array $orderItem):OrderBuilderQuery
	{
		if($this->order["orderItems"] === null)
		{
			$this->order["orderItems"] = [];
		}
		array_push($this->order["orderItems"], $orderItem);

		return $this;
	}

	public function withOrderItems(array $orderItems):OrderBuilderQuery
	{
		foreach($orderItems as $orderItem)
		{
			$this->withOrderItem($orderItem);
		}

		return $this;
	}

	public function withAddressId(int $addressId, int $type):OrderBuilderQuery
	{
		if($this->order["addresses"] === null)
		{
			$this->order["addresses"] = [];
		}

		$address = [
			"typeId"    => (int)$type,
			"addressId" => $addressId
		];
		array_push($this->order["addresses"], $address);
		return $this;
	}

	public function withRelation(string $type, int $referenceId, string $relationType):OrderBuilderQuery
	{
		if($this->order["relations"] === null)
		{
			$this->order["relations"] = [];
		}

		$relation = [
			"referenceType" => (string)$type,
			"referenceId"   => $referenceId,
			"relation"      => (string)$relationType
		];

		array_push($this->order["relations"], $relation);
		return $this;
	}

	public function withContactId(int $customerId):OrderBuilderQuery
	{
		$this->withRelation(ReferenceType::CONTACT, $customerId, RelationType::RECEIVER);
		return $this;
	}

	public function withOrderOption(int $type, int $subType, $value):OrderBuilderQuery
	{
		if($this->order["options"] === null)
		{
			$this->order["options"] = [];
		}

		$option = [
			"typeId"    => (int)$type,
			"subTypeId" => (int)$subType,
			"value"     => $value
		];

		array_push($this->order["options"], $option);
		return $this;
	}


}
