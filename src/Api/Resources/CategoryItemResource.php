<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\Facets;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class CategoryItemResource
 * @package IO\Api\Resources
 */
class CategoryItemResource extends ApiResource
{
    /**
     * CategoryItemResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Get Category Items
     * @return Response
     */
    public function index():Response
    {
        $categoryId = $this->request->get('categoryId', 0);
        $template = $this->request->get('template', '');
        
        if((int)$categoryId > 0)
        {
            $itemListOptions = [
                'page'          => $this->request->get( 'page', 1 ),
                'itemsPerPage'  => $this->request->get( 'items', 20 ),
                'sorting'       => $this->request->get( 'sorting', '' ),
                'facets'        => $this->request->get( 'facets', '' ),
                'categoryId'    => $this->request->get( 'categoryId', 1 )
            ];

            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp( ItemSearchService::class );
            $response = $itemSearchService->getResults([
                'itemList' => CategoryItems::getSearchFactory( $itemListOptions ),
                'facets'   => Facets::getSearchFactory( $itemListOptions )
            ]);
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->create( null, ResponseCode::BAD_REQUEST );
        }
        
    }
}