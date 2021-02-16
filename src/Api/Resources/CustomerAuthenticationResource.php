<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\AuthenticationService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CustomerAuthenticationResource
 *
 * Resource class for the route `io/customer/login`.
 * @package IO\Api\Resources
 */
class CustomerAuthenticationResource extends ApiResource
{
    use Loggable;

    /**
     * @var AuthenticationService $authService Instance of the AuthenticationService.
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
     * Perform the login with email and password.
     * @return Response
     */
	public function store():Response
	{
		$email    = $this->request->get('email', '');
		$password = $this->request->get('password', '');

		try
        {
            $this->authService->login((string)$email, (string)$password);
        }
        catch(\Exception $exception)
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.CustomerAuthenticationResource_loginFailed",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                    "trace" => $exception->getTraceAsString()
                ]
            );
    		$this->response->error( ResponseCode::UNAUTHORIZED, $exception->getMessage() );
            return $this->response->create(null, ResponseCode::UNAUTHORIZED);
        }

		return $this->response->create(null, ResponseCode::OK);
	}

}
