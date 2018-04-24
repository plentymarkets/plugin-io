<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\TagItems;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;

class ItemListService
{
    const TYPE_CATEGORY     = 'category';
    const TYPE_LAST_SEEN    = 'last_seen';
    const TYPE_TAG          = 'tag_list';

    public function getItemList( $type, $id, $sorting, $maxItems )
    {
        /** @var ItemSearchService $searchService */
        $searchService = pluginApp( ItemSearchService::class );
        $searchFactory = null;

        switch ($type['mobileValue'])
        {
            case self::TYPE_CATEGORY:
                $searchFactory = CategoryItems::getSearchFactory([
                    'categoryId' => $id['mobileValue'],
                    'sorting'    => $sorting['mobileValue']
                ]);
                break;
            case self::TYPE_LAST_SEEN:
                /** @var SessionStorageService $sessionStorage */
                $sessionStorage = pluginApp(SessionStorageService::class);
                $variationIds = $sessionStorage->getSessionValue(SessionStorageKeys::LAST_SEEN_ITEMS);

                $searchFactory = VariationList::getSearchFactory([
                    'variationIds'  => $variationIds['mobileValue'],
                    'sorting'       => $sorting['mobileValue']
                ]);
                break;
            case self::TYPE_TAG:
                $searchFactory = TagItems::getSearchFactory([
                    'tagIds'    => [$id]['mobileValue'],
                    'sorting'   => $sorting['mobileValue']
                ]);
                break;
            default:
                break;
        }

        if ( $maxItems > 0 )
        {
            $searchFactory->setPage(1, $maxItems['mobileValue'] );
        }

        if ( is_null($searchFactory) )
        {
            return null;
        }

        return $searchService->getResult( $searchFactory );
    }
}