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
 * Class FacetResource
 * @package IO\Api\Resources
 */
class FacetResource extends ApiResource
{
    /**
     * FacetResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Get Facets
     * @return Response
     */
    public function index():Response
    {
        $categoryId   = $this->request->get('categoryId', 0);
        $searchString = $this->request->get('query', '');

        if((int)$categoryId > 0 || strlen($searchString))
        {
            $itemListOptions = [
                'page'         => 1,
                'itemsPerPage' => 0,
                'sorting'      => '',
                'facets'       => $this->request->get('facets', '' ),
                'priceMin'     => $this->request->get('priceMin', 0),
                'priceMax'     => $this->request->get('priceMax', 0)
            ];

            if((int)$categoryId > 0)
            {
                $itemListOptions[] = ['categoryId' => $categoryId];
            }
            else
            {
                $itemListOptions[] = ['query' => $searchString,];
            }

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
