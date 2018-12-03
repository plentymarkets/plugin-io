<?php

namespace IO\Services;

use IO\Services\ItemSearch\Helper\SortingHelper;
use IO\Services\ItemSearch\SearchPresets\LiveShoppingItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
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
    public function getLiveShoppingData($liveShoppingId, $sorting = null)
    {
        if(is_null($sorting))
        {
            $sorting = 'sorting.price.avg_asc';
        }
        
        /** @var LiveShoppingRepositoryContract $liveShoppingRepo */
        $liveShoppingRepo = pluginApp(LiveShoppingRepositoryContract::class);
        /** @var LiveShopping $liveShopping */
        $liveShopping = $liveShoppingRepo->getLiveShopping($liveShoppingId);
        
        $liveShoppingItem = [];
        $liveShoppingData = [];
        
        if($liveShopping instanceof LiveShopping)
        {
            $itemList = $this->getLiveShoppingVariations($liveShopping->itemId, $sorting);
            
            if(count($itemList[0]['documents']))
            {
                $liveShoppingItem = array_slice($itemList[0]['documents'], 0, 1);
            }
            
            $liveShoppingData = $liveShopping->toArray();
            $liveShoppingData['quantitySold'] = $liveShoppingData['quantitySold'] + $liveShoppingData['quantitySoldReal'];
            unset($liveShoppingData['quantitySoldReal']);
        }
        
        return [
            'item' => !empty($liveShopping) ? $liveShoppingItem[0]['data'] : [],
            'liveShopping' => $liveShoppingData
        ];
    }
    
    public function getLiveShoppingVariations($itemId, $sorting)
    {
        $itemSearchOptions = [
            'itemId'        => $itemId,
            'sorting'       => SortingHelper::splitPathAndOrder($sorting)
        ];
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemList = $itemSearchService->getResults([
                                                       LiveShoppingItems::getSearchFactory( $itemSearchOptions )
                                                   ]);
        
       
        return $this->filterLiveShoppingVariations($itemList);
    }
    
    /**
     * @param $itemList
     * @return array
     */
    public function filterLiveShoppingVariations($itemList)
    {
        if(count($itemList))
        {
            foreach($itemList as $listKey => $list)
            {
                if(count($list['documents']))
                {
                    foreach($list['documents'] as $key => $variation)
                    {
                        if(is_null($variation['data']['prices']['specialOffer']))
                        {
                            unset($itemList[$listKey]['documents'][$key]);
                            $itemList[$listKey]['documents'][] = $variation;
                        }
                    }
                }
            }
        }
        
        return $itemList;
    }
}