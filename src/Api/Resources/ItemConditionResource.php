<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemService;


/**
 * Class ItemConditionResource
 *
 * Resource class for the route `io/item/condition`.
 * @package IO\Api\Resources
 */
class ItemConditionResource extends ApiResource
{
    /**
     * @var ItemService $itemService Instance of the ItemService.
     */
    private $itemService;

    /**
     * ItemConditionResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ItemService $itemService
     */
    public function __construct(Request $request, ApiResponse $response, ItemService $itemService)
    {
        parent::__construct($request, $response);
        $this->itemService = $itemService;
    }

    /**
     * Get the text for a specific item condition.
     * @param string $conditionId ID of the item condition.
     * @return Response
     */
    public function show(string $conditionId):Response
    {
        $conditionText = '';

        if((int)$conditionId > 0)
        {
            $conditionText = $this->itemService->getItemConditionText((int)$conditionId);
        }

        return $this->response->create($conditionText, ResponseCode::OK);
    }
}
