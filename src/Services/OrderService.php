<?php //strict

namespace IO\Services;

use IO\Constants\OrderPaymentStatus;
use IO\Models\LocalizedOrder;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Property\Contracts\OrderPropertyRepositoryContract;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use IO\Builder\Order\OrderBuilder;
use IO\Builder\Order\OrderType;
use IO\Builder\Order\OrderOptionSubType;
use IO\Builder\Order\AddressType;
use Plenty\Repositories\Models\PaginatedResult;
use IO\Constants\SessionStorageKeys;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Plugin\Http\Response;

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
     * @var FrontendPaymentMethodRepositoryContract
     */
    private $frontendPaymentMethodRepository;
    
    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     * @param BasketService $basketService
     * @param \IO\Services\SessionStorageService $sessionStorage
     */
	public function __construct(
		OrderRepositoryContract $orderRepository,
		BasketService $basketService,
        SessionStorageService $sessionStorage,
        FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
	)
	{
		$this->orderRepository = $orderRepository;
		$this->basketService   = $basketService;
        $this->sessionStorage  = $sessionStorage;
        $this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
	}

    /**
     * Place an order
     * @return LocalizedOrder
     */
	public function placeOrder():LocalizedOrder
	{
        $checkoutService = pluginApp(CheckoutService::class);
        $customerService = pluginApp(CustomerService::class);
        
        $couponCode = null;
        if(strlen($this->basketService->getBasket()->couponCode))
        {
            $couponCode = $this->basketService->getBasket()->couponCode;
        }
        
		$order = pluginApp(OrderBuilder::class)->prepare(OrderType::ORDER)
		                            ->fromBasket() //TODO: Add shipping costs & payment surcharge as OrderItem
		                            ->withStatus(3.3)
		                            ->withContactId($customerService->getContactId())
		                            ->withAddressId($checkoutService->getBillingAddressId(), AddressType::BILLING)
		                            ->withAddressId($checkoutService->getDeliveryAddressId(), AddressType::DELIVERY)
		                            ->withOrderProperty(OrderPropertyType::PAYMENT_METHOD, OrderOptionSubType::MAIN_VALUE, $checkoutService->getMethodOfPaymentId())
                                    ->withOrderProperty(OrderPropertyType::SHIPPING_PROFILE, OrderOptionSubType::MAIN_VALUE, $checkoutService->getShippingProfileId())
		                            ->done();
        
		$order = $this->orderRepository->createOrder($order, $couponCode);
        
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
	
	public function findOrderByAccessKey($orderId, $orderAccessKey)
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $redirectToLogin = $templateConfigService->get('my_account.confirmation_link_login_redirect');
    
        $order = $this->orderRepository->findOrderByAccessKey($orderId, $orderAccessKey);
        
        if($redirectToLogin == 'true')
        {
            /**
             * @var CustomerService $customerService
             */
            $customerService = pluginApp(CustomerService::class);
    
            $orderContactId = 0;
            foreach ($order->relations as $relation)
            {
                if ($relation['referenceType'] == 'contact' && (int)$relation['referenceId'] > 0)
                {
                    $orderContactId = $relation['referenceId'];
                }
            }
    
            if ((int)$orderContactId > 0)
            {
                if ((int)$customerService->getContactId() <= 0)
                {
                    return pluginApp(Response::class)->redirectTo('login?backlink=confirmation/' . $orderId . '/' . $orderAccessKey);
                }
                elseif ((int)$orderContactId !== (int)$customerService->getContactId())
                {
                    return null;
                }
            }
        }
    
        return LocalizedOrder::wrap($order, 'de');
    }

    /**
     * Get a list of orders for a contact
     * @param int $contactId
     * @param int $page
     * @param int $items
     * @param array $filters
     * @return PaginatedResult
     */
    public function getOrdersForContact(int $contactId, int $page = 1, int $items = 50, array $filters = []):PaginatedResult
    {
        $this->orderRepository->setFilters($filters);

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
    public function getLatestOrderForContact( int $contactId )
    {
        if($contactId > 0)
        {
            $order = $this->orderRepository->getLatestOrderByContactId( $contactId );
        }
        else
        {
            $order = $this->orderRepository->findOrderById($this->sessionStorage->getSessionValue(SessionStorageKeys::LATEST_ORDER_ID));
        }
        
        if(!is_null($order))
        {
            return LocalizedOrder::wrap( $order, "de" );
        }
        
        return null;
    }
    
    /**
     * Return order status text by status id
     * @param $statusId
     * @return string
     */
	public function getOrderStatusText($statusId)
    {
	    //OrderStatusTexts::$orderStatusTexts[(string)$statusId];
        return '';
    }
    
    public function getOrderPropertyByOrderId($orderId, $typeId)
    {
        /**
         * @var OrderPropertyRepositoryContract $orderPropertyRepo
         */
        $orderPropertyRepo = pluginApp(OrderPropertyRepositoryContract::class);
        return $orderPropertyRepo->findByOrderId($orderId, $typeId);
    }
    
    /**
     * List all payment methods available for switch in MyAccount
     * @param int $currentPaymentMethodId
     * @param int $orderId
     * @return \Plenty\Modules\Payment\Method\Models\PaymentMethod[]
     */
    public function getPaymentMethodListForSwitch($currentPaymentMethodId = 0, $orderId = null)
    {
        return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsListForSwitch($currentPaymentMethodId, $orderId, $this->sessionStorage->getLang());
    }
    
    /**
     * @param $paymentMethodId
     * @param int $orderId
     * @return bool
     */
	public function allowPaymentMethodSwitchFrom($paymentMethodId, $orderId = null)
	{
		/** @var TemplateConfigService $config */
		$config = pluginApp(TemplateConfigService::class);
		if ($config->get('my_account.change_payment') == "false")
		{
			return false;
		}
		if($orderId != null)
		{
			$order = $this->orderRepository->findOrderById($orderId);
			if ($order->paymentStatus !== OrderPaymentStatus::UNPAID)
			{
				// order was paid
				return false;
			}
			
			$statusId = $order->statusId;
			$orderCreatedDate = $order->createdAt;
			
			if(!($statusId <= 3.4 || ($statusId == 5 && $orderCreatedDate->toDateString() == date('Y-m-d'))))
			{
				return false;
			}
		}
		return $this->frontendPaymentMethodRepository->getPaymentMethodSwitchFromById($paymentMethodId, $orderId);
	}

    
    /**
     * @param int $orderId
     * @param int $paymentMethodId
     */
    public function switchPaymentMethodForOrder($orderId, $paymentMethodId)
    {
        if((int)$orderId > 0)
        {
            $currentPaymentMethodId = 0;
        
            $order = $this->findOrderById($orderId);
        
            $newOrderProperties = [];
            $orderProperties = $order->order->properties;
        
            if(count($orderProperties))
            {
                foreach($orderProperties as $key => $orderProperty)
                {
                    $newOrderProperties[$key] = $orderProperty;
                    if($orderProperty->typeId == OrderPropertyType::PAYMENT_METHOD)
                    {
                        $currentPaymentMethodId = (int)$orderProperty->value;
                        $newOrderProperties[$key]['value'] = (int)$paymentMethodId;
                    }
                }
            }
        
            if($paymentMethodId !== $currentPaymentMethodId)
            {
                if($this->frontendPaymentMethodRepository->getPaymentMethodSwitchFromById($currentPaymentMethodId, $orderId) && $this->frontendPaymentMethodRepository->getPaymentMethodSwitchToById($paymentMethodId))
                {
                    $order = $this->orderRepository->updateOrder(['properties' => $newOrderProperties], $orderId);
                    if(!is_null($order))
                    {
                        return LocalizedOrder::wrap( $order, "de" );
                    }
                }
            }
        }
    
        return null;
    }
}
