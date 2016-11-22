<?php //strict

namespace LayoutCore\Services;

use LayoutCore\Helper\AbstractFactory;
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
use LayoutCore\Constants\SessionStorageKeys;
use LayoutCore\Services\SessionStorageService;
use Plenty\Plugin\Http\Request;

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
     * @var SessionStorageService
     */
    private $sessionStorage;
    /**
     * @var Request
     */
    private $request;

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
		CustomerService $customerService,
        SessionStorageService $sessionStorage,
        Request $request
	)
	{
		$this->orderRepository = $orderRepository;
		$this->orderBuilder    = $orderBuilder;
		$this->basketService   = $basketService;
		$this->checkoutService = $checkoutService;
		$this->customerService = $customerService;
        $this->sessionStorage  = $sessionStorage;
        $this->request         = $request;
	}

    /**
     * Place an order
     * @return LocalizedOrder
     */
	public function placeOrder():LocalizedOrder
	{
		$order = $this->orderBuilder->prepare(OrderType::ORDER)
		                            ->fromBasket() //TODO: Add shipping costs & payment surcharge as OrderItem
		                            ->withStatus(3.3)
		                            ->withContactId($this->customerService->getContactId())
		                            ->withAddressId($this->checkoutService->getBillingAddressId(), AddressType::BILLING)
		                            ->withAddressId($this->checkoutService->getDeliveryAddressId(), AddressType::DELIVERY)
		                            ->withOrderProperty(OrderOptionType::METHOD_OF_PAYMENT, OrderOptionSubType::MAIN_VALUE, $this->checkoutService->getMethodOfPaymentId())
                                    ->withOrderProperty(OrderOptionType::SHIPPING_PROFIL, OrderOptionSubType::MAIN_VALUE, $this->checkoutService->getShippingProfileId())
		                            ->done();

		$order = $this->orderRepository->createOrder($order);
        
        if($this->customerService->getContactId() <= 0)
        {
            $this->sessionStorage->setSessionValue(SessionStorageKeys::LATEST_ORDER_ID, $order->id);
        }

        // reset basket after order was created
        $this->basketService->resetBasket();
        
        return LocalizedOrder::wrap( $order, "de" );
	}

    /**
     * Execute the payment for a given order.
     * @param int $orderId      The order id to execute payment for
     * @param int $paymentId    The MoP-ID to execute
     * @return array            An array containing a type ("succes"|"error") and a value.
     */
	public function executePayment( int $orderId, int $paymentId ):array
    {
        $paymentRepository = AbstractFactory::create( \Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract::class );
        return $paymentRepository->executePayment( $paymentId, $orderId );
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
        if($contactId > 0)
        {
            $order = $this->orderRepository->getLatestOrderByContactId( $contactId );
        }
        else
        {
            $order = $this->orderRepository->findOrderById($this->sessionStorage->getSessionValue(SessionStorageKeys::LATEST_ORDER_ID));
        }
        
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
