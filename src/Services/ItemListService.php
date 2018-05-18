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

    public function getItemList( $type, $id = null, $sorting = null, $maxItems = 0 )
    {
        /** @var ItemSearchService $searchService */
        $searchService = pluginApp( ItemSearchService::class );
        $searchFactory = null;

        if ( ( is_null($id) || strlen($id) <= 0 ) && $type !== self::TYPE_LAST_SEEN )
        {
            $searchFactory = VariationList::getSearchFactory([
                'sorting' => $sorting
            ]);
        }
        else
        {
            switch ($type)
            {
                case self::TYPE_CATEGORY:
                    $searchFactory = CategoryItems::getSearchFactory([
                        'categoryId' => $id,
                        'sorting'    => $sorting
                    ]);
                    break;
                case self::TYPE_LAST_SEEN:
                    /** @var SessionStorageService $sessionStorage */
                    $sessionStorage = pluginApp(SessionStorageService::class);
                    $variationIds = $sessionStorage->getSessionValue(SessionStorageKeys::LAST_SEEN_ITEMS);

                    $searchFactory = VariationList::getSearchFactory([
                        'variationIds'  => $variationIds,
                        'sorting'       => $sorting
                    ]);
                    break;
                case self::TYPE_TAG:
                    $searchFactory = TagItems::getSearchFactory([
                        'tagIds'    => [$id],
                        'sorting'   => $sorting
                    ]);
                    break;
                default:
                    break;
            }
        }

        if ( is_null($searchFactory) )
        {
            return null;
        }

        if ( $maxItems > 0 )
        {
            $searchFactory->setPage(1, $maxItems );
        }
        return $searchService->getResult( $searchFactory );
    }
}