<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemListService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Templates\Twig;

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
        $lastSeenContainers = [];

        $twig = pluginApp(Twig::class);

        foreach( $lastSeenItems["documents"] as $item )
        {
            $jsonItemData = json_encode($item["data"]);

            $beforePrices = $twig->renderString("{% autoescape false %}{{ container('Ceres::CategoryItem.BeforePrices', " . $jsonItemData . ") | json_encode }}{% endautoescape %}");
            $afterPrices = $twig->renderString("{% autoescape false %}{{ container('Ceres::CategoryItem.AfterPrices', " . $jsonItemData . ") | json_encode }}{% endautoescape %}");

            $beforePricesContents = json_decode($beforePrices);
            $afterPricesContents = json_decode($afterPrices);

            foreach ( $beforePricesContents as $beforePricesContent )
            {
                $lastSeenContainers[$item["id"]]["beforePrices"][] = $beforePricesContent->result;
            }
            foreach ( $afterPricesContents as $afterPricesContent )
            {
                $lastSeenContainers[$item["id"]]["afterPrices"][] = $afterPricesContent->result;
            }
        }

        return $this->response->create(["lastSeenItems" => $lastSeenItems, "containers" => $lastSeenContainers], ResponseCode::OK);
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