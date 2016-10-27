<?php //strict

namespace LayoutCore\Services;

use LayoutCore\Models\LocalizedOrder;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use LayoutCore\Builder\Order\OrderBuilder;
use LayoutCore\Builder\Order\OrderType;
use LayoutCore\Builder\Order\OrderOptionType;
use LayoutCore\Builder\Order\OrderOptionSubType;
use LayoutCore\Builder\Order\AddressType;
use LayoutCore\Constants\OrderStatusTexts;
use Plenty\Repositories\Models\PaginatedResult;

//TODO BasketService => basketItems
//TODO SessionStorageService => billingAddressId, deliveryAddressId

/**
 * Class OrderService
 * @package LayoutCore\Services
 */
class OrderService
{
	/**
	 * @var OrderRepositoryContract
	 */
	private $orderRepository;
	/**
	 * @var OrderBuilder
	 */
	private $orderBuilder;
	/**
	 * @var BasketService
	 */
	private $basketService;
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;
	/**
	 * @var CustomerService
	 */
	private $customerService;

    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param OrderBuilder $orderBuilder
     * @param \LayoutCore\Services\BasketService $basketService
     * @param \LayoutCore\Services\CheckoutService $checkoutService
     * @param \LayoutCore\Services\CustomerService $customerService
     */
	public function __construct(
		OrderRepositoryContract $orderRepository,
		OrderBuilder $orderBuilder,
		BasketService $basketService,
		CheckoutService $checkoutService,
		CustomerService $customerService
	)
	{
		$this->orderRepository = $orderRepository;
		$this->orderBuilder    = $orderBuilder;
		$this->basketService   = $basketService;
		$this->checkoutService = $checkoutService;
		$this->customerService = $customerService;
	}

    /**
     * Place an order
     * @return Order
     */
	public function placeOrder():Order
	{
		$order = $this->orderBuilder->prepare(OrderType::ORDER)
		                            ->fromBasket()
		                            ->withStatus(3.3)
		                            ->withContactId($this->customerService->getContactId())
		                            ->withAddressId($this->checkoutService->getBillingAddressId(), AddressType::BILLING)
		                            ->withAddressId($this->checkoutService->getDeliveryAddressId(), AddressType::DELIVERY)
		                            ->withOrderOption(OrderOptionType::METHOD_OF_PAYMENT, OrderOptionSubType::MAIN_VALUE, $this->checkoutService->getMethodOfPaymentId())
		                            ->done();

		return $this->orderRepository->createOrder($order);
	}

    /**
     * Find an order by ID
     * @param int $orderId
     * @return LocalizedOrder
     */
	public function findOrderById(int $orderId):LocalizedOrder
	{
		$order = $this->orderRepository->findOrderById($orderId);
        return LocalizedOrder::wrap( $order, "de" );
	}

    /**
     * Get a list of orders for a contact
     * @param int $contactId
     * @param int $page
     * @param int $items
     * @return PaginatedResult
     */
    public function getOrdersForContact(int $contactId, int $page = 1, int $items = 50):PaginatedResult
    {
        $orders = $this->orderRepository->allOrdersByContact(
            $contactId,
            $page,
            $items
        );

        return LocalizedOrder::wrapPaginated( $orders, "de" );
    }

    /**
     * Get the last order created by the current contact
     * @param int $contactId
     * @return LocalizedOrder
     */
    public function getLatestOrderForContact( int $contactId ):LocalizedOrder
    {
        $order = $this->orderRepository->getLatestOrderByContactId( $contactId );
        return LocalizedOrder::wrap( $order, "de" );
    }
    
    /**
     * Return order status text by status id
     * @param $statusId
     * @return string
     */
	public function getOrderStatusText($statusId)
    {
        return OrderStatusTexts::$orderStatusTexts[(string)$statusId];
    }
}
