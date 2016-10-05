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
    
    /**
     * OrderBuilderQuery constructor.
     * @param Application $app
     * @param BasketService $basketService
     * @param int $type
     * @param int $plentyId
     */
	public function __construct(Application $app, BasketService $basketService, int $type, int $plentyId)
	{
		$this->app           = $app;
		$this->basketService = $basketService;

		$this->order             = [];
		$this->order["typeId"]   = $type;
		$this->order["plentyId"] = $plentyId;
	}
    
    /**
     * retrun the order array
     * @return array
     */
	public function done():array
	{
		return $this->order;
	}
    
    /**
     * build order from basket data
     * @param null $basket
     * @return OrderBuilderQuery
     * @throws \Exception
     */
	public function fromBasket($basket = null):OrderBuilderQuery
	{
		if($basket === null)
		{
			$basket = $this->basketService->getBasket();
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
    
    /**
     * add status to order
     * @param float $status
     * @return OrderBuilderQuery
     */
	public function withStatus(float $status):OrderBuilderQuery
	{
		$this->order["statusId"] = $status;
		return $this;
	}
    
    /**
     * add owner to order
     * @param int $ownerId
     * @return OrderBuilderQuery
     */
	public function withOwner(int $ownerId):OrderBuilderQuery
	{
		$this->order["ownerId"] = $ownerId;
		return $this;
	}
    
    /**
     * add order item to order
     * @param array $orderItem
     * @return OrderBuilderQuery
     */
	public function withOrderItem(array $orderItem):OrderBuilderQuery
	{
		if($this->order["orderItems"] === null)
		{
			$this->order["orderItems"] = [];
		}
		array_push($this->order["orderItems"], $orderItem);

		return $this;
	}
    
    /**
     * add order items to order
     * @param array $orderItems
     * @return OrderBuilderQuery
     */
	public function withOrderItems(array $orderItems):OrderBuilderQuery
	{
		foreach($orderItems as $orderItem)
		{
			$this->withOrderItem($orderItem);
		}

		return $this;
	}
    
    /**
     * add address to order
     * @param int $addressId
     * @param int $type
     * @return OrderBuilderQuery
     */
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
    
    /**
     * add relation to order
     * @param string $type
     * @param int $referenceId
     * @param string $relationType
     * @return OrderBuilderQuery
     */
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
    
    /**
     * add comtact to order
     * @param int $customerId
     * @return OrderBuilderQuery
     */
	public function withContactId(int $customerId):OrderBuilderQuery
	{
		$this->withRelation(ReferenceType::CONTACT, $customerId, RelationType::RECEIVER);
		return $this;
	}
    
    /**
     * add order option to order
     * @param int $type
     * @param int $subType
     * @param $value
     * @return OrderBuilderQuery
     */
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
