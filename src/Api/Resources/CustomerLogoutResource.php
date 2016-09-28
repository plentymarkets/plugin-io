<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseReponse;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\AuthenticationService;

class CustomerLogoutResource extends ApiResource
{
	/**
	 * @var AuthenticationService
	 */
	private $authService;
	
	public function __construct(Request $request, ApiResponse $response, AuthenticationService $authService)
	{
		parent::__construct($request, $response);
		$this->authService = $authService;
	}
	
	public function index():BaseReponse
	{
		$this->authService->logout();
		return $this->response->create(ResponseCode::OK);
	}
	
}
