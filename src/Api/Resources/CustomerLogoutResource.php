<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\AuthenticationService;

/**
 * Class CustomerLogoutResource
 * @package LayoutCore\Api\Resources
 */
class CustomerLogoutResource extends ApiResource
{
	/**
	 * @var AuthenticationService
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
     * @return BaseResponse
     */
	public function store():BaseResponse
	{
		$this->authService->logout();
		return $this->response->create(ResponseCode::OK);
	}

}
