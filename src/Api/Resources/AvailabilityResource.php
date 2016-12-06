<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\AvailabilityService;

/**
 * Class AvailabilityResource
 * @package IO\Api\Resources
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
