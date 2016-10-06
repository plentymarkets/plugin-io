<?hh //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use Illuminate\Http\Response;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ItemService;

class VariationResource extends ApiResource
{
    private ItemService $itemService;

    public function __construct(
        Request $request,
        ApiResponse $response,
        ItemService $itemService )
    {
        parent::__construct( $request, $response );
        $this->itemService = $itemService;
    }

    public function show( string $variationId ):Response
    {
        $variation = $this->itemService->getVariation( (int) $variationId );
        return $this->response->create($variation, ResponseCode::OK);
    }
}
