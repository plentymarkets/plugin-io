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
    public function index(): Response
    {
        /** @var ItemListService $itemListService */
        $itemListService = pluginApp(ItemListService::class);

        /** @var Twig $twig */
        $twig = pluginApp(Twig::class);

        $items = $this->request->get('items', 20);
        $lastSeenItems = $itemListService->getItemList(ItemListService::TYPE_LAST_SEEN, null, null, $items);
        $lastSeenContainers = [];

        foreach ($lastSeenItems['documents'] as $item) {
            $lastSeenContainers[$item['id']]['beforePrices'] = $twig->renderString(
                "{% for content in container('Ceres::CategoryItem.BeforePrices', item) %}{{ content.result | raw }}{% endfor %}",
                ['item' => $item['data']]
            );

            $lastSeenContainers[$item['id']]['afterPrices'] = $twig->renderString(
                "{% for content in container('Ceres::CategoryItem.AfterPrices', item) %}{{ content.result | raw }}{% endfor %}",
                ['item' => $item['data']]
            );
        }

        return $this->response->create(
            ['lastSeenItems' => $lastSeenItems, 'containers' => $lastSeenContainers],
            ResponseCode::OK
        );
    }

    /**
     * @depreacted will be removed in 5.1.0 or greater
     */
    public function update(string $variationId): Response
    {
        if ((int)$variationId > 0) {
            /** @var ItemLastSeenService $itemLastSeenService */
            $itemLastSeenService = pluginApp(ItemLastSeenService::class);
            $itemLastSeenService->setLastSeenItem((int)$variationId);
        }

        return $this->index();
    }
}
