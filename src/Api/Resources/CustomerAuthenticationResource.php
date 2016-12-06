<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\AuthenticationService;

/**
 * Class CustomerAuthenticationResource
 * @package IO\Api\Resources
 */
class CustomerAuthenticationResource extends ApiResource
{
	/**
	 * @var AuthenticationService
	 */
	private $authService;

    /**
     * CustomerAuthenticationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param AuthenticationService $authService
     */
	public function __construct(Request $request, ApiResponse $response, AuthenticationService $authService)
	{
		parent::__construct($request, $response);
		$this->authService = $authService;
	}

    /**
     * Perform the login with email and password
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$email    = $this->request->get('email', '');
		$password = $this->request->get('password', '');

		$this->authService->login((string)$email, (string)$password);

		return $this->response->create(null, ResponseCode::OK);
	}

}
