<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CheckoutService;
use LayoutCore\Services\CustomerService;
use LayoutCore\Builder\Order\AddressType;

/**
 * Class CheckoutResource
 * @package LayoutCore\Api\Resources
 */
class CheckoutResource extends ApiResource
{
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;
	/**
	 * @var CustomerService
	 */
	private $customerService;

    /**
     * CheckoutResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CheckoutService $checkoutService
     * @param CustomerService $customerService
     */
	public function __construct(Request $request, ApiResponse $response, CheckoutService $checkoutService, CustomerService $customerService)
	{
		parent::__construct($request, $response);
		$this->checkoutService = $checkoutService;
		$this->customerService = $customerService;
	}

    /**
     * Get the checkout
     * @return BaseResponse
     */
	public function index():BaseResponse
	{
		$checkout = $this->checkoutService->getCheckout();
		return $this->response->create($checkout, ResponseCode::OK);
	}

    /**
     * Save adresses and set the checkout data
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$methodOfPaymentId = (int)$this->request->get("methodOfPaymentId");
		$this->checkoutService->setMethodOfPaymentId($methodOfPaymentId);

		$shippingCountryId = (int)$this->request->get("shippingCountryId");
		$this->checkoutService->setShippingCountryId($shippingCountryId);

		$shippingProfileId = (int)$this->request->get("shippingProfileId");
		$this->checkoutService->setShippingProfileId($shippingProfileId);

		$deliveryAddressData = $this->request->get("deliveryAddress", null);
		if($deliveryAddressData !== null && is_array($deliveryAddressData))
		{
			$deliveryAddress = $this->customerService->createAddress($deliveryAddressData, AddressType::DELIVERY);
			$this->checkoutService->setDeliveryAddressId($deliveryAddress->id);
		}
		else
		{
			$deliveryAddressId = (int)$this->request->get("deliveryAddressId");
			$this->checkoutService->setDeliveryAddressId($deliveryAddressId);
		}

		$billingAddressData = $this->request->get("billingAddress", null);
		if($billingAddressData !== null && is_array($billingAddressData))
		{
			$billingAddress = $this->customerService->createAddress($billingAddressData, AddressType::BILLING);
			$this->checkoutService->setBillingAddressId($billingAddress->id);
		}
		else
		{
			$billingAddressId = (int)$this->request->get("billingAddressId");
			$this->checkoutService->setBillingAddressId($billingAddressId);
		}

		return $this->index();
	}
}
