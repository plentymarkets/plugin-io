<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationAttributeMap;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ResponseCode;

/**
 * Class VariationAttributeMapResource
 *
 * Resource class for the route `io/variations/map`.
 * @package IO\Api\Resources
 */
class VariationAttributeMapResource extends ApiResource
{
    /**
     * Get variation combinations for the given item ID.
     * @return Response
     */
    public function index():Response
    {
        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = VariationAttributeMap::getSearchFactory(
            [
                'itemId' => $this->request->get('itemId'),
                'afterKey' => $this->request->get('afterKey')
            ]
        );

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);

        $variationAttributeMap = $itemSearchService->getResult($searchFactory);

        return $this->response->create($variationAttributeMap, ResponseCode::OK);
    }
}
