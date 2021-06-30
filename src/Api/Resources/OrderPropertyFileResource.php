<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class OrderPropertyFileResource
 *
 * Resource class for the route `io/order/property/file`.
 * @package IO\Api\Resources
 */
class OrderPropertyFileResource extends ApiResource
{
    /**
     * @var OrderPropertyFileService $orderPropertyFileService The instance of the OrderPropertyFileService.
     */
    private $orderPropertyFileService;

    /**
     * OrderPropertyFileResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param OrderPropertyFileService $orderPropertyFileService
     */
    public function __construct(Request $request, ApiResponse $response, OrderPropertyFileService $orderPropertyFileService)
    {
        parent::__construct($request, $response);
        $this->orderPropertyFileService = $orderPropertyFileService;
    }

    /**
     * Upload a file for an order property.
     * @return Response
     */
    public function store():Response
    {
        $file = $this->orderPropertyFileService->uploadFile($_FILES['fileData']);
        return $this->response->create($file, ResponseCode::OK);
    }
}
