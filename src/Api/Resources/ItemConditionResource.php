<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemService;


/**
 * Class ItemConditionResource
 * @package IO\Api\Resources
 */
class ItemConditionResource extends ApiResource
{
    /**
     * @var ItemService
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
     * @param string $conditionId
     * @return BaseResponse
     */
    public function show(string $conditionId):BaseResponse
    {
        $conditionText = '';
        
        if((int)$conditionId > 0)
        {
            $conditionText = $this->itemService->getItemConditionText((int)$conditionId);
        }
        
        return $this->response->create($conditionText, ResponseCode::OK);
    }
}