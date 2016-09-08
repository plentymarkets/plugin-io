<?hh //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use Illuminate\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\AuthenticationService;

class CustomerAuthenticationResource extends ApiResource
{
    private AuthenticationService $authService;

    public function __construct(Request $request, ApiResponse $response, AuthenticationService $authService )
    {
        parent::__construct( $request, $response );
        $this->authService = $authService;
    }

    public function store():Response
    {
        $email = $this->request->get('email', '');
        $password = $this->request->get('password', '');

        $this->authService->login((string)$email, (string)$password);

        return $this->response->create(null, ResponseCode::OK);
    }

}
