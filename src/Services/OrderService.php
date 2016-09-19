<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use LayoutCore\Builder\Order\OrderBuilder;
use LayoutCore\Builder\Order\OrderType;
use LayoutCore\Builder\Order\OrderOptionType;
use LayoutCore\Builder\Order\OrderOptionSubType;
use LayoutCore\Builder\Order\AddressType;
use LayoutCore\Services\BasketService;
use LayoutCore\Services\CheckoutService;
use LayoutCore\Services\CustomerService;

//TODO BasketService => basketItems
//TODO SessionStorageService => billingAddressId, deliveryAddressId

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
	
	public function findOrderById(int $orderId):Order
	{
		return $this->orderRepository->findOrderById($orderId);
	}
}
