<?hh //strict

namespace LayoutCore\Api\Resources;

use Illuminate\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\BasketService;

class BasketResource extends ApiResource
{
    private BasketService $basketService;

    public function __construct( Request $request, ApiResponse $response, BasketService $basketService )
    {
        parent::__construct( $request, $response );
        $this->basketService = $basketService;
    }

    public function index():Response
    {
        $basket = $this->basketService->getBasket();
        return $this->response->create($basket, ResponseCode::OK);
    }
}
