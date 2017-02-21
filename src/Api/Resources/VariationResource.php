<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemService;

/**
 * Class VariationResource
 * @package IO\Api\Resources
 */
class VariationResource extends ApiResource
{
    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * VariationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ItemService $itemService
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        ItemService $itemService )
    {
        parent::__construct( $request, $response );
        $this->itemService = $itemService;
    }

    /**
     * Get variation by id
     * @param string $variationId
     * @return Response
     */
    public function show( string $variationId ):Response
    {
        $variation = $this->itemService->getVariation( (int) $variationId );
        return $this->response->create($variation, ResponseCode::OK);
    }
}
