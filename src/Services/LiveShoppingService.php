<?php

namespace IO\Services;

use IO\Services\ItemSearch\Helper\SortingHelper;
use IO\Services\ItemSearch\SearchPresets\LiveShoppingItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Item\SalesPrice\Models\SalesPrice;
use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\LiveShopping\Models\LiveShopping;

/**
 * Class LiveShoppingService
 * @package IO\Services
 */
class LiveShoppingService
{
    /**
     * @param $liveShoppingId
     * @param $sorting
     * @return array
     */
    public function getLiveShoppingData($liveShoppingId, $sorting)
    {
        /** @var LiveShoppingRepositoryContract $liveShoppingRepo */
        $liveShoppingRepo = pluginApp(LiveShoppingRepositoryContract::class);
        /** @var LiveShopping $liveShopping */
        $liveShopping = $liveShoppingRepo->getByLiveShoppingId($liveShoppingId);
    
        $itemSearchOptions = [
            'itemId'        => $liveShopping->itemId,
            'sorting'       => SortingHelper::splitPathAndOrder($sorting)
        ];
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemList = $itemSearchService->getResults([
                                                       LiveShoppingItems::getSearchFactory( $itemSearchOptions )
                                                   ]);
        
        $itemList = $this->removeVariationsWithoutLiveShopping($itemList);
        
        $liveShoppingData = $liveShopping->toArray();
        
        $liveShoppingItem = array_first($itemList[0]['documents']);
        
        return [
            'item' => $liveShoppingItem['data'],
            'liveShopping' => $liveShoppingData
        ];
    }
    
    /**
     * @param $itemList
     * @return array
     */
    private function removeVariationsWithoutLiveShopping($itemList)
    {
        if(count($itemList[0]['documents']))
        {
            foreach($itemList[0]['documents'] as $key => $item)
            {
                if(is_null($item['data']['prices']['specialOffer']))
                {
                    unset($itemList[0]['documents'][$key]);
                }
            }
        }
        
        return $itemList;
    }
}