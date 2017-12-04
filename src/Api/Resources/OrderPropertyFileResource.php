<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderPropertyFileService;

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
        $fileData = $this->request->get('fileData', null);
        $response = $this->orderPropertyFileService->uploadFile($fileData);
        return $this->response->create($response, ResponseCode::OK);
    }
}
