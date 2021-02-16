<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\AuthenticationService;

/**
 * Class CustomerLogoutResource
 *
 * Resource class for the route `io/customer/logout`.
 * @package IO\Api\Resources
 */
class CustomerLogoutResource extends ApiResource
{
	/**
	 * @var AuthenticationService $authService Instance of the AuthenticationService.
	 */
	private $authService;

    /**
     * CustomerLogoutResource constructor.
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
     * Log out the current user.
     * @return Response
     */
	public function store():Response
	{
		$this->authService->logout();
		return $this->response->create(ResponseCode::OK);
	}

}
