<?php //strict

namespace IO\Api\Resources;

use IO\Services\AuthenticationService;
use IO\Services\CustomerPasswordResetService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CustomerService;

/**
 * Class CustomerPasswordResource
 * @package IO\Api\Resources
 */
class CustomerPasswordResource extends ApiResource
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var CustomerPasswordResetService
	 */
	private $customerPasswordResetService;
    /**
     * CustomerPasswordResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerService $customerService
     * @param CustomerPasswordResetService $customerPasswordResetService
     */
	public function __construct(Request $request, ApiResponse $response, CustomerService $customerService, CustomerPasswordResetService $customerPasswordResetService)
	{
		parent::__construct($request, $response);
		$this->customerService = $customerService;
		$this->customerPasswordResetService = $customerPasswordResetService;
	}

    /**
     * Set the password for the contact
     * @return Response
     */
	public function store():Response
	{
	    $oldPassword = $this->request->get('oldPassword', '');
        $newPassword = $this->request->get('password', '');
        $newPassword2 = $this->request->get('password2', '');
	    $contactId = $this->request->get('contactId', 0);
        $hash = $this->request->get('hash', '');

		if(strlen($newPassword) && strlen($newPassword2) && $newPassword == $newPassword2)
		{

		    if (!strlen($hash)) {
		        /** @var AuthenticationService $authService */
                $authService = pluginApp(AuthenticationService::class);

                if(!$authService->checkPassword($oldPassword))
                {
                    unset($this->response->eventData['AfterAccountAuthentication']);
                    $response = $this->response->create("Invalid password", ResponseCode::UNAUTHORIZED);

                    return $response;
                }
            }
		    elseif (!$this->customerPasswordResetService->checkHash($contactId, $hash))
            {
                return $this->response->create(null, ResponseCode::BAD_REQUEST);
            }

			$result = $this->customerService->updatePassword($newPassword, $contactId, $hash);

            if($result === null)
            {
                return $this->response->create($result, ResponseCode::BAD_REQUEST);
            }

			return $this->response->create($result, ResponseCode::OK);
		}

		$this->response->error(4, "Missing password or new passwords are not equal");
		return $this->response->create(null, ResponseCode::BAD_REQUEST);
	}

}
