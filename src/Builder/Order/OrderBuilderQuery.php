<?php
namespace IO\Builder\Order;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Application;

/**
 * Class OrderBuilderQuery
 * @package IO\Builder\Order
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
     * Return the order array
     * @return array
     */
	public function done():array
	{
		return $this->order;
	}

    /**
     * Build order from basket data
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

		// Add basket items to order
		$orderItemBuilder = $this->app->make(OrderItemBuilder::class);
		if(!$orderItemBuilder instanceof OrderItemBuilder)
		{
			throw new \Exception("Error while instantiating OrderItemBuilder.");
		}

		$items = $this->basketService->getBasketItems();

		if(!is_array($items))
		{
			throw new \Exception("Error while reading item data from basket");
		}

		$this->withOrderItems(
			$orderItemBuilder->fromBasket($basket, $items)
		);

		return $this;
	}

    /**
     * Add the status to the order
     * @param float $status
     * @return OrderBuilderQuery
     */
	public function withStatus(float $status):OrderBuilderQuery
	{
		$this->order["statusId"] = $status;
		return $this;
	}

    /**
     * Add the owner to the order
     * @param int $ownerId
     * @return OrderBuilderQuery
     */
	public function withOwner(int $ownerId):OrderBuilderQuery
	{
		$this->order["ownerId"] = $ownerId;
		return $this;
	}

    /**
     * Add an order item to the order
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
     * Add order items to the order
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
     * Add an address to the order
     * @param int $addressId
     * @param int $type
     * @return OrderBuilderQuery
     */
	public function withAddressId(int $addressId, int $type):OrderBuilderQuery
	{
		if($this->order["addressRelations"] === null)
		{
			$this->order["addressRelations"] = [];
		}

		$address = [
			"typeId"    => (int)$type,
			"addressId" => $addressId
		];
		array_push($this->order["addressRelations"], $address);
		return $this;
	}

    /**
     * Add the relation to the order
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
     * Add a contact to the order
     * @param int $customerId
     * @return OrderBuilderQuery
     */
	public function withContactId(int $customerId):OrderBuilderQuery
	{
		$this->withRelation(ReferenceType::CONTACT, $customerId, RelationType::RECEIVER);
		return $this;
	}

    /**
     * Add an order option to the order
     * @param int $type
     * @param int $subType
     * @param $value
     * @return OrderBuilderQuery
     */
	public function withOrderProperty(int $type, int $subType, $value):OrderBuilderQuery
	{
		if($this->order["properties"] === null)
		{
			$this->order["properties"] = [];
		}

		$option = [
			"typeId"    => (int)$type,
			"subTypeId" => (int)$subType,
			"value"     => (string)$value
		];

		array_push($this->order["properties"], $option);
		return $this;
	}


}
