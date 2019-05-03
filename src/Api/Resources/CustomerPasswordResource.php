<?php //strict

namespace IO\Api\Resources;

use IO\Services\AuthenticationService;
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
     * CustomerPasswordResource constructor.
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

		if(strlen($newPassword) && strlen($newPassword2) && $newPassword == $newPassword2 && $this->isValidPassword($newPassword))
		{
		    if (!strlen($hash))
		    {
		        /** @var AuthenticationService $authService */
                $authService = pluginApp(AuthenticationService::class);

                if(!$authService->checkPassword($oldPassword))
                {
                    unset($this->response->eventData['AfterAccountAuthentication']);
                    $response = $this->response->create("Invalid password", ResponseCode::UNAUTHORIZED);

                    return $response;
                }
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

    /**
     * Checks if the password meets the requirements
     */
    static public function isValidPassword($password)
    {
        $passwordPattern = '/^(?=.*[A-Za-z])(?=.*\d)\S{8,}$/';
        return preg_match($passwordPattern,$password);
    }
}
