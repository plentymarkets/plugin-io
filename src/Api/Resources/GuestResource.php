<?php //strict

namespace IO\Api\Resources;

use IO\Services\CustomerService;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class GuestResource
 *
 * Resource class for the route `io/guest`.
 * @package IO\Api\Resources
 */
class GuestResource extends ApiResource
{
    /**
     * @var CustomerService $customerService Instance of the CustomerService.
     */
    private $customerService;

    /**
     * GuestResource constructor.
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
     * Logs the user in as a guest.
     * @return Response
     */
    public function store(): Response
    {
        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        $email = $this->request->get('email', '');
        $existingEmail = $sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL);

        if (!is_null($existingEmail) && strlen($existingEmail) && $email !== $existingEmail) {
            $this->customerService->deleteGuestAddresses();
        }

        $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL, $email);

        return $this->response->create($email, ResponseCode::OK);
    }
}
