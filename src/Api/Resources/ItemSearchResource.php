<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\SearchItems;
use IO\Services\ItemLoader\Loaders\Facets;

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
     * @return Response
     */
    public function index():Response
    {
        $searchString = $this->request->get('searchString', '');
        $template = $this->request->get('template', '');
        
        if(strlen($searchString))
        {
            $response = pluginApp(ItemLoaderService::class)
                ->loadForTemplate($template, [SearchItems::class, Facets::class], [
                    'searchString'  => $searchString,
                    'page'          => $this->request->get('page', 1),
                    'itemsPerPage'  => $this->request->get('itemsPerPage', 20),
                    'orderBy'       => $this->request->get('orderBy', 'itemName'),
                    'orderByKey'    => $this->request->get('orderByKey', 'ASC'),
                    'facets'        => $this->request->get('facets', '')
                ]);
    
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->error(1, '');
        }
        
    }
}