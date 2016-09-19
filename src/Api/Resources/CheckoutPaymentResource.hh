<?hh //strict
namespace LayoutCore\Api\Resources;
use Illuminate\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CheckoutService;
class CheckoutPaymentResource extends ApiResource
{
    private CheckoutService $checkoutService;
    public function __construct( Request $request, ApiResponse $response, CheckoutService $checkoutService )
    {
        parent::__construct( $request, $response );
        $this->checkoutService = $checkoutService;
    }
    public function store():Response
    {
        $event = $this->checkoutService->preparePayment();
        return $this->response->create( $event, ResponseCode::OK );
    }
}
