<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SingleItem;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

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

    /**
     * Get variation by ID.
     * @param string $variationId The ID of the variation to get.
     * @return Response
     */
    public function show(string $variationId): Response
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        $variation = $itemSearchService->getResults(
            SingleItem::getSearchFactory(
                [
                    'variationId' => $variationId,
                    'setPriceOnly' => $this->request->get('setPriceOnly') === 'true'
                ]
            )
        );

        return $this->response->create($variation, ResponseCode::OK);
    }
}
