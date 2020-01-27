<?php

namespace IO\Services;

use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use IO\Services\ItemSearch\SearchPresets\BasketItems;
use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\CrossSellingItems;
use IO\Services\ItemSearch\SearchPresets\ManufacturerItems;
use IO\Services\ItemSearch\SearchPresets\TagItems;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\CachingRepository;

class ItemListService
{
    const TYPE_CATEGORY     = 'category';
    const TYPE_LAST_SEEN    = 'last_seen';
    const TYPE_TAG          = 'tag_list';
    const TYPE_RANDOM       = 'random';
    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_CROSS_SELLER = 'cross_selling';
    const TYPE_WISH_LIST    = 'wish_list';

    public function getItemList( $type, $id = null, $sorting = null, $maxItems = 0, $crossSellingRelationType = null)
    {
        /** @var ItemSearchService $searchService */
        $searchService = pluginApp( ItemSearchService::class );
        $searchFactory = null;

        if ( !$this->isValidId( $id ) && !(in_array($type, [self::TYPE_LAST_SEEN, self::TYPE_CROSS_SELLER, self::TYPE_WISH_LIST] )))
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
                $basketRepository = pluginApp(BasketRepositoryContract::class);

                $variationIds = $cachingRepository->get(SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $basketRepository->load()->id);
                $variationIds = array_slice($variationIds, 0, $maxItems);

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
            case self::TYPE_MANUFACTURER:
                $searchFactory = ManufacturerItems::getSearchFactory([
                    'manufacturerId' => $id,
                    'page' => 1,
                    'itemsPerPage' => $maxItems,
                    'sorting'   => $sorting
                ]);
                break;
            case self::TYPE_CROSS_SELLER:
                if(!isset($itemId) || !strlen($itemId)) {
                    /** @var CategoryService $categoryService */
                    $categoryService = app(CategoryService::class);

                    $currentItem = $categoryService->getCurrentItem();
                    $id = $currentItem['item']['id'] ?? 0;
                }
                $searchFactory = CrossSellingItems::getSearchFactory([
                    'itemId' => $id ,
                    'relation' => $crossSellingRelationType,
                    'sorting' => $sorting
                ]);
                break;
            case self::TYPE_WISH_LIST:
                /** @var ItemWishListService $wishListService */
                $wishListService = pluginApp(ItemWishListService::class);
                $wishListVariationIds = $wishListService->getItemWishList();

                $searchFactory = BasketItems::getSearchFactory([
                    'variationIds'  => $wishListVariationIds,
                    'quantities'    => 1,
                    'itemsPerPage'  => count($wishListVariationIds)
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

        $itemListResult = $searchService->getResult( $searchFactory );

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if($shopBuilderRequest->isShopBuilder())
        {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemListResult = $searchResultFactory->fillSearchResults(
                $itemListResult,
                null
            );
        }

        return $itemListResult;
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
