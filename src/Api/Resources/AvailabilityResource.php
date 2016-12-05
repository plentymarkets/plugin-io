<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\AvailabilityService;

/**
 * Class AvailabilityResource
 * @package LayoutCore\Api\Resources
 */
class AvailabilityResource extends ApiResource
{
    /**
     * @var AvailabilityService
     */
    private $availabilityService;
    
    /**
     * AvailabilityResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param AvailabilityService $availabilityService
     */
    public function __construct(Request $request, ApiResponse $response, AvailabilityService $availabilityService)
    {
        parent::__construct($request, $response);
        $this->availabilityService = $availabilityService;
    }
    
    /**
     * Get the Availability by Id
     * @param string $availabilityId
     * @return BaseResponse
     */
    public function show(string $availabilityId):BaseResponse
    {
        $availability = null;
        
        if((int)$availabilityId > 0)
        {
            $availability = $this->availabilityService->getAvailabilityById((int)$availabilityId);
        }
    
        return $this->response->create($availability, ResponseCode::OK);
    }
}
