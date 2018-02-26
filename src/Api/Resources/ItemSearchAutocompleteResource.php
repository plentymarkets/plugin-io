<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemSearch\SearchPresets\SearchItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class ItemSearchResource
 * @package IO\Api\Resources
 */
class ItemSearchAutocompleteResource extends ApiResource
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
        $searchString = $this->request->get('query', '');
        
        if(strlen($searchString))
        {
            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp( ItemSearchService::class );
            $response = $itemSearchService->getResults(
                SearchItems::getSearchFactory([
                    'query'         => $searchString,
                    'autocomplete'  => true,
                    'page'          => 1,
                    'itemsPerPage'  => 20
                ])
            );
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->create( null, ResponseCode::BAD_REQUEST );
        }
        
    }
}