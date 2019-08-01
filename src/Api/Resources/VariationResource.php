<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
/**
 * Class VariationResource
 * @package IO\Api\Resources
 */
class VariationResource extends ApiResource
{
    /**
     * VariationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response )
    {
        parent::__construct( $request, $response );
    }

    public function index():Response
    {
        $variations = [];

        $template = $this->request->get('template', '');
        
        if(strlen($template))
        {
            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp( ItemSearchService::class );
            $variations = $itemSearchService->getResults(
                VariationList::getSearchFactory([
                    'variationIds'  => $this->request->get('variationIds' ),
                    'sorting'       => $this->request->get( 'sorting' ),
                    'sortingField'  => $this->request->get( 'sortingField' ),
                    'sortingOrder'  => $this->request->get( 'sortingOrder' ),
                    'page'          => $this->request->get( 'page' ),
                    'itemsPerPage'  => $this->request->get( 'itemsPerPage' )
                ])
            );
        }

        return $this->response->create($variations, ResponseCode::OK);
    }

    /**
     * Get variation by id
     * @param string $variationId
     * @return Response
     */
    public function show( string $variationId ):Response
    {
        $variation = [];
        
        $template = $this->request->get('template', '');
        if(strlen($template))
        {
            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp( ItemSearchService::class );
            $variation = $itemSearchService->getResults(
                SingleItem::getSearchFactory([
                    'variationId' => $variationId
                ])
            );
        }
        
        return $this->response->create($variation, ResponseCode::OK);
    }
}
