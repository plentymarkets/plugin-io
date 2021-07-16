<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\AvailabilityService;

/**
 * Class AvailabilityResource
 *
 * Resource class for the route `io/item/availability`.
 * @package IO\Api\Resources
 */
class AvailabilityResource extends ApiResource
{
    /**
     * @var AvailabilityService $availabilityService Instance of the AvailabilityService.
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
     * Get the availability by ID.
     * @param string $availabilityId
     * @return Response
     */

    public function show(string $availabilityId):Response
    {
        $availability = null;

        if((int)$availabilityId > 0)
        {
            $availability = $this->availabilityService->getAvailabilityById((int)$availabilityId);
        }

        return $this->response->create($availability, ResponseCode::OK);
    }
}
