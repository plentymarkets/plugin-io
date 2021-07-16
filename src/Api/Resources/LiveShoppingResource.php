<?php

namespace IO\Api\Resources;

use IO\Services\LiveShoppingService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class LiveShoppingResource
 *
 * Resource class for the route `io/live-shopping`.
 * @package IO\Api\Resources
 */
class LiveShoppingResource extends ApiResource
{
    /** @var LiveShoppingService $liveShoppingService */
    private $liveShoppingService;
    
    public function __construct(Request $request, ApiResponse $response, LiveShoppingService $liveShoppingService)
    {
        parent::__construct($request, $response);
        $this->liveShoppingService = $liveShoppingService;
    }
    
    public function show(string $liveShoppingId):Response
    {
        $sorting = $this->request->get('sorting', null);
        $liveShoppingData = [];
        if((int)$liveShoppingId > 0)
        {
            $liveShoppingData = $this->liveShoppingService->getLiveShoppingData($liveShoppingId, $sorting);
        }
        
        return $this->response->create($liveShoppingData, ResponseCode::OK);
    }
}