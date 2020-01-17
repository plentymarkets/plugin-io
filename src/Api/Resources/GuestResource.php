<?php //strict

namespace IO\Api\Resources;

use IO\Services\CustomerService;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\SessionStorageKeys;

class GuestResource extends ApiResource
{
    /** @var CustomerService $customerService */
    private $customerService;

    public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
    {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
    }

    public function store(): Response
    {
        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        $email = $this->request->get('email', '');
        $existingEmail = $sessionStorageRepository->getSessionValue(SessionStorageKeys::GUEST_EMAIL);

        if (!is_null($existingEmail) && strlen($existingEmail) && $email !== $existingEmail) {
            $this->customerService->deleteGuestAddresses();
        }

        $sessionStorageRepository->setSessionValue(SessionStorageKeys::GUEST_EMAIL, $email);

        return $this->response->create($email, ResponseCode::OK);
    }
}
