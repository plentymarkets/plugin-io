<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Builder\Category\CategoryParamsBuilder;
use LayoutCore\Services\ItemService;

/**
 * Class ItemSearchResource
 * @package LayoutCore\Api\Resources
 */
class ItemSearchResource extends ApiResource
{
    /**
     * ItemSearchResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Search items
     * @return BaseResponse
     */
    public function index():BaseResponse
    {
        $searchString = $this->request->get('searchString', '');
        
        if(strlen($searchString))
        {
            $page         = $this->request->get('page', 1);
    
            $params = [
                'itemsPerPage' => $this->request->get('itemsPerPage', 20),
                'orderBy'      => $this->request->get('orderBy', 'itemName'),
                'orderByKey'   => $this->request->get('orderByKey', 'ASC')
            ];
    
            /**
             * @var CategoryParamsBuilder $categoryParamsBuilder
             */
            $categoryParamsBuilder = pluginApp(CategoryParamsBuilder::class);
            /**
             * @var ItemService $itemService
             */
            $itemService           = pluginApp(ItemService::class);
    
            $response = $itemService->searchItems($searchString, $categoryParamsBuilder->fromArray($params), $page);
    
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->error(1, '');
        }
        
    }
}