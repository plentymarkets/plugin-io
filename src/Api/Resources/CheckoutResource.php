<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use IO\Builder\Order\AddressType;

/**
 * Class CheckoutResource
 *
 * Resource class for the route `io/checkout`.
 * @package IO\Api\Resources
 */
class CheckoutResource extends ApiResource
{
	/**
	 * @var CheckoutService $checkoutService Instance of the CheckoutService.
	 */
	private $checkoutService;

    /**
     * @var CustomerService Instance of the CustomerService.
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
     * Get all relevant information for the checkout view.
     * @return Response
     */
	public function index():Response
	{
		$checkout = $this->checkoutService->getCheckout();
		return $this->response->create($checkout, ResponseCode::OK);
	}

    /**
     * Save adresses and set the checkout data
     * @return Response
     */

    /**
     * Set the selected method of payment, shipping country ID, shipping profile ID, and the delivery address.
     * Create addresses if required.
     * @return Response
     */
	public function store():Response
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

    /**
     * Update the checkout information.
     * @return Response
     */

    /**
     * Update the checkout information.
     * @param string $selector Unused.
     * @return Response
     */
	public function update(string $selector = ''):Response
    {
        $billingAddressId = (int)$this->request->get("billingAddressId");
        $this->checkoutService->setBillingAddressId($billingAddressId);

        $deliveryAddressId = (int)$this->request->get("deliveryAddressId");
        $this->checkoutService->setDeliveryAddressId($deliveryAddressId);

        $methodOfPaymentId = (int)$this->request->get("methodOfPaymentId");
        $this->checkoutService->setMethodOfPaymentId($methodOfPaymentId);

        $shippingProfileId = (int)$this->request->get("shippingProfileId");
        $this->checkoutService->setShippingProfileId($shippingProfileId);

        return $this->index();
    }
}
