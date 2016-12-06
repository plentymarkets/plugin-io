<?php //strict

namespace IO\Services;

use IO\Models\LocalizedOrder;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use IO\Builder\Order\OrderBuilder;
use IO\Builder\Order\OrderType;
use IO\Builder\Order\OrderOptionType;
use IO\Builder\Order\OrderOptionSubType;
use IO\Builder\Order\AddressType;
use IO\Constants\OrderStatusTexts;
use Plenty\Repositories\Models\PaginatedResult;
use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;

/**
 * Class OrderService
 * @package IO\Services
 */
class OrderService
{
	/**
	 * @var OrderRepositoryContract
	 */
	private $orderRepository;
	/**
	 * @var BasketService
	 */
	private $basketService;
    /**
     * @var SessionStorageService
     */
    private $sessionStorage;
    
    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param BasketService $basketService
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
	public function __construct(
		OrderRepositoryContract $orderRepository,
		BasketService $basketService,
        SessionStorageService $sessionStorage
	)
	{
		$this->orderRepository = $orderRepository;
		$this->basketService   = $basketService;
        $this->sessionStorage  = $sessionStorage;
	}

    /**
     * Place an order
     * @return LocalizedOrder
     */
	public function placeOrder():LocalizedOrder
	{
        $checkoutService = pluginApp(CheckoutService::class);
        $customerService = pluginApp(CustomerService::class);
        
		$order = pluginApp(OrderBuilder::class)->prepare(OrderType::ORDER)
		                            ->fromBasket() //TODO: Add shipping costs & payment surcharge as OrderItem
		                            ->withStatus(3.3)
		                            ->withContactId($customerService->getContactId())
		                            ->withAddressId($checkoutService->getBillingAddressId(), AddressType::BILLING)
		                            ->withAddressId($checkoutService->getDeliveryAddressId(), AddressType::DELIVERY)
		                            ->withOrderProperty(OrderOptionType::METHOD_OF_PAYMENT, OrderOptionSubType::MAIN_VALUE, $checkoutService->getMethodOfPaymentId())
                                    ->withOrderProperty(OrderOptionType::SHIPPING_PROFIL, OrderOptionSubType::MAIN_VALUE, $checkoutService->getShippingProfileId())
		                            ->done();

		$order = $this->orderRepository->createOrder($order);
        
        if($customerService->getContactId() <= 0)
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
        $paymentRepository = pluginApp( PaymentMethodRepositoryContract::class );
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
