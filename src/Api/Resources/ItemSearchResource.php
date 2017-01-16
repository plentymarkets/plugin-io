<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\SearchItems;

/**
 * Class ItemSearchResource
 * @package IO\Api\Resources
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
        $template = $this->request->get('template', '');
        
        if(strlen($searchString))
        {
            $response = pluginApp(ItemLoaderService::class)
                ->loadForTemplate($template, [SearchItems::class], [
                    'searchString'  => $searchString,
                    'page'          => $this->request->get('page', 1),
                    'itemsPerPage'  => $this->request->get('itemsPerPage', 20),
                    'orderBy'       => $this->request->get('orderBy', 'itemName'),
                    'orderByKey'    => $this->request->get('orderByKey', 'ASC')
                ]);
    
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->error(1, '');
        }
        
    }
}