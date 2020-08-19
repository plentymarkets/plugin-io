<?php

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\ItemSearch\SearchPresets\Facets;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

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
    public function index(): Response
    {
        $searchString = $this->request->get('query', '');
        $facets  = $this->request->get('facets', '');

        if (strlen($searchString) || strlen($facets)) {
            $itemListOptions = [
                'page' => $this->request->get('page', 1),
                'itemsPerPage' => $this->request->get('items', 20),
                'sorting' => $this->request->get('sorting', ''),
                'facets' => $facets,
                'query' => $searchString,
                'priceMin' => $this->request->get('priceMin', 0),
                'priceMax' => $this->request->get('priceMax', 0),
            ];

            /** @var ItemSearchService $itemSearchService */
            $itemSearchService = pluginApp(ItemSearchService::class);
            $response = $itemSearchService->getResults(
                [
                    'itemList' => SearchItems::getSearchFactory($itemListOptions),
                    'facets' => Facets::getSearchFactory($itemListOptions)
                ]
            );

            return $this->response->create($response, ResponseCode::OK);
        } else {
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }
    }
}
