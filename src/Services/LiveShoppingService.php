<?php

namespace IO\Services;

use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\LiveShopping\Models\LiveShopping;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\LiveShoppingItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Class LiveShoppingService
 * @package IO\Services
 */
class LiveShoppingService
{
    private $ownStock = false;
    private $ownLimitation = false;

    /**
     * @param $liveShoppingId
     * @param $sorting
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

            if (count($itemList[0]['documents'])) {
                $liveShoppingItem = array_slice($itemList[0]['documents'], 0, 1);
                $liveShoppingItem = $liveShoppingItem[0]['data'];
            }

            $liveShoppingData = $liveShopping->toArray();
            $liveShoppingData['quantitySold'] += $liveShoppingData['quantitySoldReal'];
            unset($liveShoppingData['quantitySoldReal']);

            $this->checkStockLimit($liveShoppingData, $liveShoppingItem);

            if ($this->ownStock) {
                unset($liveShoppingItem['stock']);
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

    public function getLiveShoppingVariations($itemId, $sorting)
    {
        /** @var SortingHelper $sortingHelper */
        $sortingHelper = pluginApp(SortingHelper::class);

        $itemSearchOptions = [
            'itemId' => $itemId,
            'sorting' => $sortingHelper->splitPathAndOrder($sorting),
            'resultFields' => $this->buildResultFields()
        ];
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
     * @param $itemList
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
     * @param $data
     * @param $item
     */
    private function checkStockLimit(&$data, $item)
    {
        $isStockLimited = $item['variation']['stockLimitation'] === 1;
        $isNetStockLess = (int)$item['stock']['net'] < $data['quantityMax'] - $data['quantitySold'];

        if ($isStockLimited && $isNetStockLess) {
            $data['quantitySold'] = $data['quantityMax'] - $item['stock']['net'];
        }
    }

    private function buildResultFields()
    {
        $resultFields = ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_LIST_ITEM);

        if (!(in_array('stock.net', $resultFields) || in_array('stock.*', $resultFields))) {
            $resultFields[] = 'stock.net';
            $this->ownStock = true;
        }

        if (!(in_array('variation.stockLimitation', $resultFields) || in_array('variation.*', $resultFields))) {
            $resultFields[] = 'variation.stockLimitation';
            $this->ownLimitation = true;
        }

        return $resultFields;
    }
}
