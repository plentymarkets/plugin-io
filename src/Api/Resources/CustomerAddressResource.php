<?php //strict

namespace IO\Api\Resources;

use IO\Services\BasketService;
use IO\Services\NotificationService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;
use IO\Builder\Order\AddressType;

/**
 * Class CustomerAddressResource
 *
 * Resource class for the route `io/customer/address`.
 * @package IO\Api\Resources
 */
class CustomerAddressResource extends ApiResource
{
    /**
     * @var int ADDRESS_NOT_SET Dummy id, if no address is select, or to unselect an address.
     */
    const ADDRESS_NOT_SET = -99;

    /**
     * @var CustomerService $customerService Instance of the CustomerService.
     */
    private $customerService;

    /**
     * CustomerAddressResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerService $customerService
     */
    public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
    {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
    }

    /**
     * Get the address type from the current request.
     * @return int Type of the address.
     */
    private function getAddressType(): int
    {
        return (INT)$this->request->get("typeId", null);
    }

    /**
     * Get an address of the customer by type.
     * @return Response
     */
    public function index(): Response
    {
        $type = $this->getAddressType();
        $addresses = $this->customerService->getAddresses($type);
        return $this->response->create($addresses, ResponseCode::OK);
    }

    /**
     * Create an address with the given type.
     * @return Response
     */
    public function store(): Response
    {
        $address = null;

        $address = $this->request->all();
        $addressId = $address['id'];
        $type = $this->getAddressType();

        if (is_null($type)) {
            $this->response->error(0, "Missing type id.");
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        try {
            if (!is_null($addressId) && (int)$addressId > 0) {
                $newAddress = $this->customerService->updateAddress((int)$addressId, $address, (int)$type);
            } else {
                $newAddress = $this->customerService->createAddress($address, $type);
            }
        } catch (\Plenty\Exceptions\ValidationException $validationException) {
            throw $validationException;
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [210, 211])) {
                $this->response->error($exception->getCode(), $exception->getMessage());
            } else {
                $this->response->error(0, $exception->getMessage());
            }
            return $this->response->create($exception, ResponseCode::UNPROCESSABLE_ENTITY);
        }

        return $this->response->create($newAddress, ResponseCode::CREATED);
    }

    /**
     * Update the address with the given address ID.
     * @param string $addressId ID of the address to update.
     * @return Response
     */
    public function update(string $addressId): Response
    {
        $type = $this->getAddressType();
        if (is_null($type)) {
            $this->response->error(0, "Missing type id.");
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);

        if ((int)$addressId > 0 || (int)$addressId == static::ADDRESS_NOT_SET) {
            if ($type == AddressType::BILLING) {
                $basketService->setBillingAddressId((int)$addressId);
            } elseif ($type == AddressType::DELIVERY) {
                $basketService->setDeliveryAddressId((int)$addressId);
            }
        }

        return $this->response->create($addressId, ResponseCode::OK);
    }

    /**
     * Delete the address with the given ID.
     * @param string $addressId ID of the address to delete.
     * @return Response
     */
    public function destroy(string $addressId): Response
    {
        $type = $this->getAddressType();
        if (is_null($type)) {
            $this->response->error(0, "Missing type id.");
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        $addressId = (int)$addressId;
        $this->customerService->deleteAddress($addressId, $type);

        return $this->index();
    }
}
