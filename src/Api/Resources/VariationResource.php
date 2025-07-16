<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SingleItem;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemSearch\SearchPresets\ShopTheLookPreset;
use IO\Services\ItemUpdateService;
/**
 * Class VariationResource
 *
 * Resource class for the route `io/variations`.
 * @package IO\Api\Resources
 */
class VariationResource extends ApiResource
{
    /**
     * VariationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Return a list of items with the given parameters.
     * @return Response
     */
    public function index(): Response
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);

        $searchFactory = VariationList::getSearchFactory(
            [
                'variationIds' => $this->request->get('variationIds'),
                'sorting' => $this->request->get('sorting'),
                'sortingField' => $this->request->get('sortingField'),
                'sortingOrder' => $this->request->get('sortingOrder'),
                'page' => $this->request->get('page'),
                'itemsPerPage' => $this->request->get('itemsPerPage'),
                'setPriceOnly' => $this->request->get('setPriceOnly') === 'true',
                'withVariationPropertyGroups' => true,
                'withOrderPropertySelectionValues' => true
            ]
        );

        $resultFieldTemplate = $this->request->get('resultFieldTemplate', '');
        if (strlen($resultFieldTemplate)) {
            $searchFactory->withResultFields(
                ResultFieldTemplate::load('Webshop.ResultFields.' . $resultFieldTemplate)
            );
        }

        $variations = $itemSearchService->getResults($searchFactory);

        return $this->response->create($variations, ResponseCode::OK);
    }

    public function update(string $variationId): Response 
    {
        $variationId = (int) $variationId;
        $data = $this->request->get("data", null);

        try {
            $itemUpdateService = pluginApp(ItemUpdateService::class);
            $variation = $itemUpdateService->updateVariation($data, $variationId);
            return $this->response->create($variation, ResponseCode::OK);
        } catch(Exception $e) {
             return $this->response->create(['error bk_2529a'], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get variation by ID.
     * @param string $variationId The ID of the variation to get.
     * @return Response
     */
    public function show(string $variationId): Response
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        
        $shopTheLook = $this->request->get('shopTheLook', null);
        if(!is_null($shopTheLook) && $shopTheLook === "true")
        {

            // SHOP THE LOOK
            // +
            // +
            // +
            $variation = $itemSearchService->getResults(
                BasketItems::getSearchFactory(
                    [
                        'variationIds' => [$variationId]
                    ]
                )->withReducedResults( true )
                ->withPrices()
                ->withResultFields("Ceres::ResultFields.ShopTheLook")
            );
            // +
            // +
            // +
            // +


        } else {
            $variation = $itemSearchService->getResults(
                SingleItem::getSearchFactory(
                    [
                        'variationId' => $variationId,
                        'setPriceOnly' => $this->request->get('setPriceOnly') === 'true'
                    ]
                )
            );
        
        }
        return $this->response->create($variation, ResponseCode::OK);
    }
}
