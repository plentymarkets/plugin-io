<?php

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\TagItems;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\CachingRepository;

class ItemListService
{
    const TYPE_CATEGORY     = 'category';
    const TYPE_LAST_SEEN    = 'last_seen';
    const TYPE_TAG          = 'tag_list';
    const TYPE_RANDOM       = 'random';

    public function getItemList( $type, $id = null, $sorting = null, $maxItems = 0 )
    {
        /** @var ItemSearchService $searchService */
        $searchService = pluginApp( ItemSearchService::class );
        $searchFactory = null;

        if ( !$this->isValidId( $id ) && $type !== self::TYPE_LAST_SEEN )
        {
            $type = self::TYPE_RANDOM;
        }

        switch ($type)
        {
            case self::TYPE_CATEGORY:
                $searchFactory = CategoryItems::getSearchFactory([
                    'categoryId' => is_array( $id ) ? $id[0] : $id,
                    'sorting'    => $sorting
                ]);
                break;
            case self::TYPE_LAST_SEEN:
                /** @var CachingRepository $cachingRepository */
                $cachingRepository = pluginApp(CachingRepository::class);
                $variationIds = $cachingRepository->get(SessionStorageKeys::LAST_SEEN_ITEMS);

                if ( !is_null($variationIds) && count($variationIds) > 0 )
                {
                    $searchFactory = VariationList::getSearchFactory([
                        'variationIds'      => $variationIds,
                        'sorting'           => $sorting,
                        'excludeFromCache'  => true
                    ]);
                }
                break;
            case self::TYPE_TAG:
                $searchFactory = TagItems::getSearchFactory([
                    'tagIds'    => is_array( $id ) ? $id : [$id],
                    'sorting'   => $sorting
                ]);
                break;
            case self::TYPE_RANDOM:
                $searchFactory = VariationList::getSearchFactory([
                    'sorting'       => $sorting
                ]);
                break;
            default:
                break;
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

    private function isValidId( $id )
    {
        if ( is_array( $id ) )
        {
            return count( $id ) > 0 && $this->isValidId( $id[0] );
        }
        else
        {
            return !is_null( $id ) && strlen( $id ) > 0;
        }
    }

}