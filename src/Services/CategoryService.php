<?php //strict

namespace IO\Services;

use Illuminate\Support\Collection;
use IO\Constants\CategoryType;
use IO\Helper\CategoryDataFilter;
use IO\Helper\MemoryCache;
use IO\Services\ItemLoader\Services\LoadResultFields;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\CategoryClient;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Plugin\Application;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class CategoryService
 * @package IO\Services
 */
class CategoryService
{
    use MemoryCache;
    use LoadResultFields;

    /**
     * @var CategoryRepositoryContract
     */
    private $categoryRepository;

    /**
     * @var WebstoreConfigurationService
     */
    private $webstoreConfig;

    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

    // is set from controllers
    /**
     * @var Category
     */
    private $currentCategory = null;
    /**
     * @var array
     */
    private $currentCategoryTree = [];

    private $currentItem = [];

    /**
     * CategoryService constructor.
     * @param CategoryRepositoryContract $category
     */
    public function __construct(CategoryRepositoryContract $categoryRepository, WebstoreConfigurationService $webstoreConfig, SessionStorageService $sessionStorageService)
    {
        $this->categoryRepository    = $categoryRepository;
        $this->webstoreConfig 		 = $webstoreConfig;
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * Set the current category by ID.
     * @param int $catID The id of the current category
     */
    public function setCurrentCategoryID(int $catID = 0)
    {
        $this->setCurrentCategory(
            $this->categoryRepository->get($catID, $this->sessionStorageService->getLang())
        );
    }

    /**
     * Set the current category by ID.
     * @param Category $cat The current category
     */
    public function setCurrentCategory($cat)
    {
        $lang = $this->sessionStorageService->getLang();
        $this->currentCategory     = null;
        $this->currentCategoryTree = [];

        if($cat === null)
        {
            return;
        }

        // List parent/open categories
        $this->currentCategory = $cat;
        while($cat !== null)
        {
            $this->currentCategoryTree[$cat->level] = $cat;
            $cat                                    = $this->categoryRepository->get($cat->parentCategoryId, $lang);
        }
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->currentCategory;
    }

    /**
     * Get a category by ID
     * @param int $catID The category ID
     * @param string $lang The language to get the category
     * @return Category
     */
    public function get($catID = 0, $lang = null)
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $category = $this->fromMemoryCache(
            "category.$catID.$lang",
            function() use ($catID, $lang) {
                $category = $this->categoryRepository->get($catID, $lang);

                $currentDetail = [];
                foreach($category->details as $detail)
                {
                    if($detail->plentyId == pluginApp(Application::class)->getPlentyId())
                    {
                        $currentDetail = $detail;
                    }
                }

                if(count($currentDetail))
                {
                    $category->details = pluginApp(Collection::class, [ [$currentDetail] ]);
                }

                return $category;
            }
        );

        return $category;
    }

    public function getForPlentyId($catID = 0, $lang = null, $plentyId = null)
    {
        $category = $this->get( $catID, $lang );
        if ( is_null($plentyId) )
        {
            $plentyId = pluginApp(Application::class)->getPlentyId();
        }

        if ( !is_null($category) )
        {
            /** @var CategoryClient $categoryClient */
            foreach ( $category->clients as $categoryClient )
            {
                if ( $categoryClient->plentyId === (int)$plentyId )
                {
                    return $category;
                }
            }
        }

        return null;
    }

    public function getChildren($categoryId, $lang = null)
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $children = $this->fromMemoryCache(
            "categoryChildren.$categoryId.$lang",
            function() use ($categoryId, $lang) {
                if($categoryId > 0)
                {
                    return $this->categoryRepository->getChildren($categoryId, $lang);
                }

                return null;
            }
        );

        return $children;
    }

    /**
     * Return the URL for a given category ID.
     * @param Category $category the category to get the URL for
     * @param string $lang the language to get the URL for
     * @return string|null
     */
    public function getURL($category, $lang = null)
    {
        $defaultLanguage = $this->webstoreConfig->getDefaultLanguage();
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$category->id.$lang",
            function() use ($category, $lang, $defaultLanguage) {
                if(!$category instanceof Category || $category->details[0] === null)
                {
                    return null;
                }
                $categoryURL = pluginApp(
                    UrlQuery::class,
                    ['path' => $this->categoryRepository->getUrl($category->id, $lang), 'lang' => $lang]
                );
                return $categoryURL->toRelativeUrl($lang !== $defaultLanguage);
            }
        );

        return $categoryUrl;
    }

    public function getURLById($categoryId, $lang = null)
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }
        return $this->getURL($this->get($categoryId, $lang), $lang);
    }

    /**
     * @param $category
     * @param $lang
     * @return CategoryDetails|null
     */
    public function getDetails($category, $lang)
    {
        if ( $category === null )
        {
            return null;
        }

        /** @var CategoryDetails $catDetail */
        foreach( $category->details as $catDetail )
        {
            if ( $catDetail->lang == $lang )
            {
                return $catDetail;
            }
        }

        return null;
    }


    /**
     * Check whether a category is referenced by the current route
     * @param int $catID The ID for the category to check
     * @return bool
     */
    public function isCurrent(Category $category):bool
    {
        if($this->currentCategory === null)
        {
            return false;
        }
        return $this->currentCategory->id === $category->id;
    }

    /**
     * Check whether any child of a category is referenced by the current route
     * @param Category $category The category to check
     * @return bool
     */
    public function isOpen(Category $category):bool
    {
        if($this->currentCategory === null)
        {
            return false;
        }

        foreach($this->currentCategoryTree as $lvl => $categoryBranch)
        {
            if($categoryBranch->id === $category->id)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether a category or any of its children is referenced by the current route
     * @param Category $category The category to check
     * @return bool
     */
    public function isActive(Category $category = null):bool
    {
        return $category !== null && ($this->isCurrent($category) || $this->isOpen($category));
    }

    /**
     * @param Category $category
     * @param array $params
     * @param int $page
     * @return null|PaginatedResult
     */
    public function getItems( $category = null, array $params = [], int $page = 1 )
    {
        if( $category == null )
        {
            $category = $this->currentCategory;
        }

        if( $category == null || $params == null )
        {
            return null;
        }

        /**
         * @var ItemService $itemService
         */
        $itemService = pluginApp(ItemService::class);
        return $itemService->getItemForCategory( $category->id, $params, $page );
    }

    /**
     * Return the sitemap tree as an array
     * @param string|array   $type     Only return categories of given types
     * @param string         $lang     The language to get sitemap tree for
     * @param int|null       $maxLevel The deepest category level to load
     * @param int $customerClassId The customer class id to get tree
     * @return array
     */
    public function getNavigationTree($type = null, string $lang = null, int $maxLevel = 2, int $customerClassId = 0):array
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        if ( is_null( $type ) )
        {
            $type = CategoryType::ALL;
        }

        $tree = $this->filterCategoriesByTypes(
            $this->categoryRepository->getLinklistTree($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId, $maxLevel, $customerClassId),
            $type
        );

        return pluginApp( CategoryDataFilter::class )->applyResultFields(
            $tree,
            $this->loadResultFields( ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_CATEGORY_TREE ) )
        );
    }

    /**
     * Return the sitemap list as an array
     * @param string|array  $type Only return categories of given type
     * @param string        $lang The language to get sitemap list for
     * @return array
     */
    public function getNavigationList($type = "all", string $lang = null):array
    {
        if ( $lang === null )
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $list = $this->filterCategoriesByTypes(
            $this->categoryRepository->getLinklistList($type, $lang, $this->webstoreConfig->getWebstoreConfig()->webstoreId)
        );


        return pluginApp( CategoryDataFilter::class )->applyResultFields(
            $list,
            $this->loadResultFields( ResultFieldTemplate::get( ResultFieldTemplate::TEMPLATE_CATEGORY_TREE ) )
        );
    }

    private function filterCategoriesByTypes( $categoryList = [], $types = CategoryType::ALL )
    {
        if ( is_string($types) )
        {
            if ( $types === CategoryType::ALL )
            {
                $types = [
                    CategoryType::BLOG,
                    CategoryType::CONTAINER,
                    CategoryType::CONTENT,
                    CategoryType::ITEM
                ];
            }
            else
            {
                $types = [$types];
            }
        }

        $result = array_filter(
            $categoryList,

            function($category) use ($types)
            {
                return in_array($category->type, $types);
            }
        );

        $result = array_map(
            function($category) use ($types)
            {
                /** @var $category Category */
                $category->children = $this->filterCategoriesByTypes($category->children, $types);

                return $category;
            },
            $result
        );

        return $result;
    }

    /**
     * Returns a list of all parent categories including given category
     * @param int   $catID      The category Id to get the parents for or 0 to use current category
     * @param bool  $bottomUp   Set true to order result from bottom (deepest category) to top (= level 1)
     * @return array            The parents of the category
     */
    public function getHierarchy( int $catID = 0, bool $bottomUp = false ):array
    {
        if( $catID > 0 )
        {
            $this->setCurrentCategoryID( $catID );
        }

        $hierarchy = [];

        /**
         * @var Category $category
         */
        foreach ( $this->currentCategoryTree as $lvl => $category )
        {
            array_push( $hierarchy, $category );
        }

        if( $bottomUp === false )
        {
            $hierarchy = array_reverse( $hierarchy );
        }

        if(count($this->currentItem))
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
            array_push( $hierarchy, $this->currentItem['texts'][$lang] );
        }

        return $hierarchy;
    }

    public function isVisibleForWebstore( $category, $webstoreId = null, $lang = null )
    {
        if ( is_null($lang) )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        if ( is_null($webstoreId) )
        {
            /** @var WebstoreConfigurationService $webstoreService */
            $webstoreService = pluginApp(WebstoreConfigurationService::class);
            $webstoreId = $webstoreService->getWebstoreConfig()->webstoreId;
        }


        return $category->clients->where('plenty_webstore_category_link_webstore_id', $webstoreId)->first() instanceof CategoryClient
            && $category->details->where('lang', $lang)->first() instanceof CategoryDetails;
    }

    public function setCurrentItem($item)
    {
        $this->currentItem = $item;
    }

    public function getCurrentItem()
    {
        return $this->currentItem;
    }
}
