<?php

namespace IO\Services;

use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\LiveShopping\Models\LiveShopping;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\LiveShoppingItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Service Class LiveShoppingService
 *
 * This service class contains functions related to the live shopping functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class LiveShoppingService
{
    private $ownStock = false;
    private $ownLimitation = false;

    /**
     * Get required data for live shopping in the frontend
     * @param int $liveShoppingId A live shopping id
     * @param string|null $sorting Optional: Type of sorting for live shopping variations
     * @return array
     */
    public function getLiveShoppingData($liveShoppingId, $sorting = null)
    {
        if (is_null($sorting)) {
            $sorting = 'sorting.price.avg_asc';
        }

        /** @var LiveShoppingRepositoryContract $liveShoppingRepo */
        $liveShoppingRepo = pluginApp(LiveShoppingRepositoryContract::class);
        /** @var LiveShopping $liveShopping */
        $liveShopping = $liveShoppingRepo->getLiveShopping($liveShoppingId);

        $liveShoppingItem = [];

        if ($liveShopping instanceof LiveShopping) {
            $itemList = $this->getLiveShoppingVariations($liveShopping->itemId, $sorting);

            if (is_array($itemList[0]['documents']) && count($itemList[0]['documents'])) {
                $liveShoppingItem = array_slice($itemList[0]['documents'], 0, 1);
                $liveShoppingItem = $liveShoppingItem[0]['data'];
            }

            $liveShoppingData = $liveShopping->toArray();
            $liveShoppingData['quantitySold'] += $liveShoppingData['quantitySoldReal'];
            unset($liveShoppingData['quantitySoldReal']);

            $this->checkStockLimit($liveShoppingData, $liveShoppingItem);

            if ($this->ownStock) {
                unset($liveShoppingItem['stock']['net']);
            }

            if ($this->ownLimitation) {
                unset($liveShoppingItem['variation']['stockLimitation']);
            }

            return [
                'item' => $liveShoppingItem,
                'liveShopping' => $liveShoppingData
            ];
        }

        return null;
    }

    /**
     * Get list of variations for live shopping
     * @param int $itemId An item id
     * @param string|null $sorting Sorting for live shopping variations
     * @return array|object
     * @throws \Exception
     */
    public function getLiveShoppingVariations($itemId, $sorting)
    {
        $itemSearchOptions = [
            'itemId' => $itemId,
            'resultFields' => $this->buildResultFields()
        ];
        /** @var SortingHelper $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);

        $sorting = $sortingHelper->getSorting($sorting);
        if (is_array($sorting) && count($sorting) == 1) {
            $itemSearchOptions['sorting'] = ['path' => $sorting[0]['field'], 'order' => $sorting[0]['order']];
        }
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp(ItemSearchService::class);
        $itemList = $itemSearchService->getResults(
            [
                LiveShoppingItems::getSearchFactory($itemSearchOptions)
            ]
        );

        return $this->filterLiveShoppingVariations($itemList);
    }

    /**
     * Filter list of variations for a specialOffer price
     * @param array $itemList
     * @return array
     */
    public function filterLiveShoppingVariations($itemList)
    {
        if (count($itemList)) {
            foreach ($itemList as $listKey => $list) {
                if (count($list['documents'])) {
                    foreach ($list['documents'] as $key => $variation) {
                        if (is_null($variation['data']['prices']['specialOffer'])) {
                            unset($itemList[$listKey]['documents'][$key]);
                            $itemList[$listKey]['documents'][] = $variation;
                        }
                    }
                }
            }
        }

        return $itemList;
    }


    /**
     * Check if item is limited to net stock and modify quantitySold to reflect the limited stock
     *
     * @param array $data
     * @param array $item
     */
    private function checkStockLimit(&$data, $item)
    {
        $isStockLimited = $item['variation']['stockLimitation'] === 1;
        $isNetStockLess = (int)$item['stock']['net'] < $data['quantityMax'] - $data['quantitySold'];

        if ($isStockLimited && $isNetStockLess) {
            $data['quantitySold'] = $data['quantityMax'] - $item['stock']['net'];
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function buildResultFields()
    {
        $resultFields = ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_LIST_ITEM);

        $stockNet = ['*', 'stock.*', 'stock.net'];
        if (empty(array_intersect($stockNet, $resultFields))) {
            $resultFields[] = 'stock.net';
            $this->ownStock = true;
        }

        $stockLimitation = ['*', 'variation.*', 'variation.stockLimitation'];
        if (empty(array_intersect($stockLimitation, $resultFields))) {
            $resultFields[] = 'variation.stockLimitation';
            $this->ownLimitation = true;
        }


        return $resultFields;
    }
}
