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
 * @package IO\Api\Resources
 */
class OrderPropertyFileResource extends ApiResource
{
    private $orderPropertyFileService;
    
    public function __construct(Request $request, ApiResponse $response, OrderPropertyFileService $orderPropertyFileService)
    {
        parent::__construct($request, $response);
        $this->orderPropertyFileService = $orderPropertyFileService;
    }
    
    
    public function store():Response
    {
        $file = $this->orderPropertyFileService->uploadFile($_FILES['fileData']);
        return $this->response->create($file, ResponseCode::OK);
    }
}
