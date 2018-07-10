<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemListService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

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
        $items = $this->request->get("items", 4);
        $lastSeenItems = pluginApp(ItemListService::class)->getItemList(ItemListService::TYPE_LAST_SEEN, null, null, $items);

        return $this->response->create($lastSeenItems, ResponseCode::OK);
    }

    public function update(string $variationId):Response
    {
        if((int)$variationId > 0)
        {
            $itemLastSeenService = pluginApp(ItemLastSeenService::class);
            $itemLastSeenService->setLastSeenItem((int)$variationId);
        }

        return $this->index();
    }
}