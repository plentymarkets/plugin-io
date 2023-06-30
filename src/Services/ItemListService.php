<?php

namespace IO\Services;

use IO\Services\ItemSearch\Factories\VariationSearchResultFactory;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\CategoryItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\CrossSellingItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\ManufacturerItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchSuggestions;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\TagItems;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\CachingRepository;

/**
 * Service Class ItemListService
 *
 * This service class contains function related to item listings.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Service
 */
class ItemListService
{
    const TYPE_CATEGORY = 'category';
    const TYPE_LAST_SEEN = 'last_seen';
    const TYPE_TAG = 'tag_list';
    const TYPE_RANDOM = 'random';
    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_CROSS_SELLER = 'cross_selling';
    const TYPE_WISH_LIST = 'wish_list';
    const TYPE_SEARCH_SUGGESTIONS = 'search_suggestions';
    const TYPE_ALL_ITEMS = 'all';

    /**
     * Gets a list of items based on parameters
     * @param string $type Type of item list
     * @param int|null $id Optional: Contains an identifier depending on the type
     * @param string|null $sorting Optional: Type of sorting
     * @param int $maxItems Optional: Maximum number of items (Default: 0)
     * @param string|null $crossSellingRelationType Optional: Type of cross selling relation
     * @param bool $withCategories Optional: If true, load category data (Default: false)
     * @return array|null
     * @throws \Exception
     */
    public function getItemList(
        $type,
        $id = null,
        $sorting = null,
        $maxItems = 0,
        $crossSellingRelationType = null,
        $withCategories = false
    )
    {
        /** @var ItemSearchService $searchService */
        $searchService = pluginApp(ItemSearchService::class);
        $searchFactory = null;

        if (!$this->isValidId($id) && !(in_array(
                $type,
                [
                    self::TYPE_LAST_SEEN,
                    self::TYPE_CROSS_SELLER,
                    self::TYPE_WISH_LIST,
                    self::TYPE_SEARCH_SUGGESTIONS,
                    self::TYPE_ALL_ITEMS
                ]
            ))) {
            $type = self::TYPE_RANDOM;
        }

        switch ($type) {
            case self:: TYPE_ALL_ITEMS:
                 $searchFactory = CategoryItems::getSearchFactory(
                    [
                        'categoryId' => null,
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_CATEGORY:
                $searchFactory = CategoryItems::getSearchFactory(
                    [
                        'categoryId' => is_array($id) ? $id[0] : $id,
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_LAST_SEEN:
                /** @var CachingRepository $cachingRepository */
                $cachingRepository = pluginApp(CachingRepository::class);
                $basketRepository = pluginApp(BasketRepositoryContract::class);

                $variationIds = $cachingRepository->get(
                    SessionStorageRepositoryContract::LAST_SEEN_ITEMS . '_' . $basketRepository->load()->sessionId,
                    []
                );
                $variationIds = array_slice($variationIds ?? [], 0, $maxItems);

                if (count($variationIds) > 0) {
                    $searchFactory = VariationList::getSearchFactory(
                        [
                            'variationIds' => $variationIds,
                            'sorting' => $sorting,
                            'excludeFromCache' => true,
                            'withVariationPropertyGroups' => true
                        ]
                    );
                }
                break;
            case self::TYPE_TAG:
                $searchFactory = TagItems::getSearchFactory(
                    [
                        'tagIds' => is_array($id) ? $id : [$id],
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_RANDOM:
                $searchFactory = VariationList::getSearchFactory(
                    [
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_MANUFACTURER:
                $searchFactory = ManufacturerItems::getSearchFactory(
                    [
                        'manufacturerId' => $id,
                        'page' => 1,
                        'itemsPerPage' => $maxItems,
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_CROSS_SELLER:
                if (!isset($id) || !strlen($id)) {
                    /** @var CategoryService $categoryService */
                    $categoryService = pluginApp(CategoryService::class);

                    $currentItem = $categoryService->getCurrentItem();
                    $id = $currentItem['item']['id'] ?? 0;
                }
                $searchFactory = CrossSellingItems::getSearchFactory(
                    [
                        'itemId' => $id,
                        'relation' => $crossSellingRelationType,
                        'sorting' => $sorting
                    ]
                );
                break;
            case self::TYPE_WISH_LIST:
                /** @var ItemWishListService $wishListService */
                $wishListService = pluginApp(ItemWishListService::class);
                $wishListVariationIds = $wishListService->getItemWishList();

                $searchFactory = BasketItems::getSearchFactory(
                    [
                        'variationIds' => $wishListVariationIds,
                        'quantities' => 1,
                        'itemsPerPage' => count($wishListVariationIds)
                    ]
                );
                break;
            case self::TYPE_SEARCH_SUGGESTIONS:
                $searchFactory = SearchSuggestions::getSearchFactory(
                    [
                        'query' => '',
                    ]
                );
                break;
            default:
                break;
        }

        if (is_null($searchFactory)) {
            return null;
        }

        if ($withCategories) {
            $searchFactory->withCategories();
        }

        if ($maxItems > 0) {
            $searchFactory->setPage(1, $maxItems);
        }

        $itemListResult = $searchService->getResult($searchFactory);

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ($shopBuilderRequest->isShopBuilder()) {
            /** @var VariationSearchResultFactory $searchResultFactory */
            $searchResultFactory = pluginApp(VariationSearchResultFactory::class);
            $itemListResult = $searchResultFactory->fillSearchResults(
                $itemListResult,
                null
            );
        }

        return $itemListResult;
    }

    private function isValidId($id)
    {
        if (is_array($id)) {
            return count($id) > 0 && $this->isValidId($id[0]);
        } else {
            return !is_null($id) && strlen($id) > 0;
        }
    }

}
