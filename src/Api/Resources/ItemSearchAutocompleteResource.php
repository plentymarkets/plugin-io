<?php

namespace IO\Api\Resources;

use IO\Services\ItemSearchAutocompleteService;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class ItemSearchAutocompleteResource
 *
 * Resource class for the route `io/item/search/autocomplete`.
 * @package IO\Api\Resources
 */
class ItemSearchAutocompleteResource extends ApiResource
{
    /**
     * ItemSearchAutocompleteResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Execute a search for a list of autocomplete suggestions for a given query.
     * @return Response
     */
    public function index(): Response
    {
        $searchString = $this->request->get('query', '');

        if (strlen($searchString)) {
            $searchTypes = $this->request->get('types', []);

            /** @var ItemSearchAutocompleteService $itemSearchAutocompleteService */
            $itemSearchAutocompleteService = pluginApp(ItemSearchAutocompleteService::class);
            $response = $itemSearchAutocompleteService->transformResult($itemSearchAutocompleteService->getResults($searchString, $searchTypes));

            return $this->response->create($response, ResponseCode::OK);
        }

        return $this->response->create(null, ResponseCode::BAD_REQUEST);
    }
}
