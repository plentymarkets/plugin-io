<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemLoader\Loaders\ItemURLs;
use IO\Services\ItemLoader\Loaders\LastSeenItemList;
use IO\Services\ItemSearch\SearchPresets\Facets;
use IO\Services\ItemSearch\SearchPresets\SearchItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLastSeenService;

/**
 * Class ItemLastSeenResource
 * @package IO\Api\Resources
 */
class ItemLastSeenResource extends ApiResource
{
    /**
     * ItemLastSeenResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Return last seen items
     * @return Response
     */
    public function index():Response
    {
        $templateName = $this->request->get('templateName');
        $options = $this->request->get('options');
        
        $lastSeenItems = pluginApp(ItemLoaderService::class)->loadForTemplate($templateName, [
            "single" => [
                LastSeenItemList::class
            ],
            "multi" => [
                'itemURLs' => ItemURLs::class
            ]
        ], $options);
        
        return $this->response->create($lastSeenItems, ResponseCode::OK);
    }

    /**
     * @param string $selector
     * @return Response
     */
    public function update(string $selector):Response
    {
        $variationId = $this->request->get('variationId');

        if((int)$variationId > 0)
        {
            $itemLastSeenService = pluginApp(ItemLastSeenService::class);
            $itemLastSeenService->setLastSeenItem( $variationId );
        }

        return $this->response->create("", ResponseCode::OK);
    }
}