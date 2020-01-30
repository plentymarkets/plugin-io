<?php //strict

namespace IO\Services;

use IO\Constants\CategoryType;
use IO\Guards\AuthGuard;
use IO\Helper\ArrayHelper;
use IO\Helper\CategoryDataFilter;
use IO\Helper\MemoryCache;
use IO\Helper\Utils;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\CategoryClient;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helper\LoadResultFields;
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

    /** @var WebstoreConfigurationRepositoryContract */
    private $webstoreConfigurationRepository;

    /** @var ContactRepositoryContract $contactRepository */
    private $contactRepository;

    // is set from controllers
    /**
     * @var Category
     */
    private $currentCategory = null;
    /**
     * @var array
     */
    private $currentCategoryTree = [];

    private $authGuard;

    private $currentItem = [];

    /**
     * @var null|int
     */
    private $webstoreId = null;

    /**
     * CategoryService constructor.
     * @param CategoryRepositoryContract $categoryRepository
     * @param WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
     * @param AuthGuard $authGuard
     * @param ContactRepositoryContract $contactRepository
     */
    public function __construct(
        CategoryRepositoryContract $categoryRepository,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository,
        AuthGuard $authGuard,
        ContactRepositoryContract $contactRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
        $this->authGuard = $authGuard;
        $this->webstoreId = Utils::getWebstoreId();
        $this->contactRepository = $contactRepository;
    }

    /**
     * Set the current category by ID.
     * @param int $catID The id of the current category
     */
    public function setCurrentCategoryID(int $catID = 0)
    {
        $this->setCurrentCategory(
            $this->categoryRepository->get($catID, Utils::getLang(), $this->webstoreId)
        );
    }

    /**
     * Set the current category by ID.
     * @param Category $cat The current category
     */
    public function setCurrentCategory($cat)
    {
        $lang = Utils::getLang();
        $this->currentCategory = null;
        $this->currentCategoryTree = [];

        if ($cat === null) {
            return;
        }

        // List parent/open categories
        $this->currentCategory = $cat;
        while ($cat !== null) {
            $this->currentCategoryTree[$cat->level] = $cat;
            $cat = $this->categoryRepository->get($cat->parentCategoryId, $lang, $this->webstoreId);
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
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $webstoreId = $this->webstoreId;
        $category = $this->fromMemoryCache(
            "category.$catID.$lang",
            function () use ($catID, $lang, $webstoreId) {
                $category = $this->categoryRepository->get($catID, $lang, $webstoreId);
                return $category;
            }
        );

        return $category;
    }

    public function getForPlentyId($catID = 0, $lang = null, $plentyId = null)
    {
        $category = $this->get($catID, $lang);
        if (is_null($plentyId)) {
            $plentyId = Utils::getPlentyId();
        }

        if (!is_null($category)) {
            /** @var CategoryClient $categoryClient */
            foreach ($category->clients as $categoryClient) {
                if ($categoryClient->plentyId === (int)$plentyId) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * @param $categoryId
     * @param null $lang
     * @return mixed
     *
     * @deprecated
     */
    public function getChildren($categoryId, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $children = $this->fromMemoryCache(
            "categoryChildren.$categoryId.$lang",
            function () use ($categoryId, $lang) {
                if ($categoryId > 0) {
                    return $this->categoryRepository->getChildren($categoryId, $lang);
                }

                return null;
            }
        );

        return $children;
    }

    public function getCurrentCategoryChildren()
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $category = $this->getCurrentCategory();
        $branches = ArrayHelper::toArray($category->branch);
        $children = $this->getNavigationTree(
            $templateConfigService->get('header.showCategoryTypes'),
            Utils::getLang(),
            $category->level + 1,
            $this->contactRepository->getContactClassId()
        );

        for ($level = 1; $level <= 6; $level++) {
            if (!is_null($branches) && $branches['category' . $level . 'Id'] > 0) {
                foreach ($children as $childrenCategory) {
                    if ($childrenCategory['id'] === $branches['category' . $level . 'Id']) {
                        $children = $childrenCategory['children'];
                        break;
                    }
                }
            } else {
                break;
            }
        }

        return $children;
    }

    /**
     * Return the URL for a given category ID.
     * @param Category $category the category to get the URL for
     * @param string $lang the language to get the URL for
     * @param int |null $webstoreId
     * @return string|null
     */
    public function getURL($category, $lang = null, $webstoreId = null)
    {
        $defaultLanguage = $this->webstoreConfigurationRepository->getDefaultLanguage();
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        if (is_null($webstoreId)) {
            $webstoreId = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId;
        }

        $categoryUrl = $this->fromMemoryCache(
            "categoryUrl.$category->id.$lang.$webstoreId",
            function () use ($category, $lang, $defaultLanguage, $webstoreId) {
                if (!$category instanceof Category || $category->details->first() === null) {
                    return null;
                }
                $categoryURL = pluginApp(
                    UrlQuery::class,
                    [
                        'path' => $this->categoryRepository->getUrl($category->id, $lang, false, $webstoreId),
                        'lang' => $lang
                    ]
                );
                return $categoryURL->toRelativeUrl($lang !== $defaultLanguage);
            }
        );

        return $categoryUrl;
    }

    public function getURLById($categoryId, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
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
        if ($category === null) {
            return null;
        }

        /** @var CategoryDetails $catDetail */
        foreach ($category->details as $catDetail) {
            if ($catDetail->lang == $lang) {
                return $catDetail;
            }
        }

        return null;
    }


    /**
     * Check whether a category is referenced by the current route
     * @param mixed $category The category to check
     * @return bool
     */
    public function isCurrent($category): bool
    {
        if ($this->currentCategory === null) {
            return false;
        }

        $categoryId = ($category instanceof Category) ? $category->id : $category['id'];

        return $this->currentCategory->id === $categoryId;
    }

    /**
     * Check whether any child of a category is referenced by the current route
     * @param mixed $category The category to check
     * @return bool
     */
    public function isOpen($category): bool
    {
        if ($this->currentCategory === null) {
            return false;
        }

        $categoryId = ($category instanceof Category) ? $category->id : $category['id'];

        foreach ($this->currentCategoryTree as $lvl => $categoryBranch) {
            if ($categoryBranch->id === $categoryId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether a category or any of its children is referenced by the current route
     * @param mixed $category The category to check
     * @return bool
     */
    public function isActive($category = null): bool
    {
        return $category !== null && ($this->isCurrent($category) || $this->isOpen($category));
    }

    /**
     * @param Category $category
     * @param array $params
     * @param int $page
     * @return null|PaginatedResult
     */
    public function getItems($category = null, array $params = [], int $page = 1)
    {
        if ($category == null) {
            $category = $this->currentCategory;
        }

        if ($category == null || $params == null) {
            return null;
        }

        /**
         * @var ItemService $itemService
         */
        $itemService = pluginApp(ItemService::class);
        return $itemService->getItemForCategory($category->id, $params, $page);
    }

    /**
     * Return the sitemap tree as an array
     * @param string|array $type Only return categories of given types
     * @param string $lang The language to get sitemap tree for
     * @param int|null $maxLevel The deepest category level to load
     * @param int $customerClassId The customer class id to get tree
     * @return array
     */
    public function getNavigationTree(
        $type = null,
        string $lang = null,
        int $maxLevel = 2,
        int $customerClassId = 0
    ): array {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        if (is_null($type)) {
            $type = CategoryType::ALL;
        }

        $tree = $this->categoryRepository->getArrayTree(
            $type,
            $lang,
            $this->webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId,
            $maxLevel,
            $customerClassId,
            function ($category) {
                return $category['linklist'] == 'Y';
            }
        );

        if (Utils::isContactLoggedIn() === false && Utils::isAdminPreview() === false) {
            $tree = $this->filterVisibleCategories($tree);
        }

        $categoryDataFilter = pluginApp(CategoryDataFilter::class);
        return $categoryDataFilter->applyResultFields(
            $tree,
            ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_CATEGORY_TREE)
        );
    }

    private function filterVisibleCategories($categoryList = [])
    {
        $result = array_filter(
            $categoryList,
            function ($category) {
                return $category['right'] !== 'customer';
            }
        );

        $result = array_map(
            function ($category) {
                /** @var $category Category */
                $category->children = $this->filterVisibleCategories($category->children);

                return $category;
            },
            $result
        );

        return $result;
    }

    public function getPartialTree($categoryId = 0, $type = CategoryType::ALL)
    {
        if ($categoryId > 0) {
            $currentCategory = $this->get($categoryId);
            $branch = $currentCategory->branch->toArray();
            $maxLevel = max($currentCategory->level + 3, 6);

            $tree = $this->getNavigationTree(
                $type,
                Utils::getLang(),
                $maxLevel,
                $this->contactRepository->getContactClassId()
            );

            // Filter categories not having texts in current language
            $tree = array_filter(
                $tree,
                function ($category) {
                    return count($category['details']);
                }
            );

            $tree = array_values($tree);
            $filteredTree = $this->filterBranchEntries($tree, $branch);

            if (count($filteredTree)) {
                return $filteredTree;
            } else {
                return $this->getPartialTree(null, $type);
            }
        } else {
            $tree = $this->getNavigationTree(
                $type,
                Utils::getLang(),
                3,
                $this->contactRepository->getContactClassId()
            );

            // Filter categories not having texts in current language
            $tree = array_filter(
                $tree,
                function ($category) {
                    return count($category['details']);
                }
            );

            $tree = array_values($tree);
            $siblingCount = count($tree);

            foreach ($tree as $i => $category) {
                $this->appendBranchFields($tree[$i], $siblingCount, '', 1);
            }

            return $tree;
        }
    }


    private function filterBranchEntries($tree, $branch = [], $level = 1, $urlPrefix = '')
    {
        $branchKey = "category" . $level . "Id";
        $isCurrentLevel = $branch[$branchKey] === $branch["categoryId"];
        $result = [];
        $siblingCount = count($tree);

        foreach ($tree as $category) {
            $isInBranch = $category['id'] === $branch[$branchKey];

            // filter children by current branch
            if ($isInBranch && !$isCurrentLevel) {
                $this->appendBranchFields($category, $siblingCount, $urlPrefix, 6);
                $category['children'] = $this->filterBranchEntries(
                    $category['children'],
                    $branch,
                    $level + 1,
                    $category['url']
                );
                $result[] = $category;
            } else {
                if ($isInBranch && $isCurrentLevel) {
                    $this->appendBranchFields($category, $siblingCount, $urlPrefix, 2);
                    $result[] = $category;
                } else {
                    if (!$isInBranch && $isCurrentLevel) {
                        $this->appendBranchFields($category, $siblingCount, $urlPrefix, 0);
                        $result[] = $category;
                    }
                }
            }
        }

        return $result;
    }

    private function appendBranchFields(&$category, $siblingCount = 1, $urlPrefix = '', $depth = 6)
    {
        // Filter children not having texts in current language
        $category['children'] = array_filter(
            $category['children'],
            function ($child) {
                return count($child['details']);
            }
        );
        $category['children'] = array_values($category['children']);

        // add flags for lazy loading
        $category['childCount'] = count($category['children']);
        $category['siblingCount'] = $siblingCount;

        // add url
        $details = $category['details'][0];

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $urlPrefix]);
        $category['url'] = $urlQuery->join($details['nameUrl'])->toRelativeUrl();

        if (count($category['children']) && $depth > 0) {
            foreach ($category['children'] as $i => $child) {
                $this->appendBranchFields(
                    $category['children'][$i],
                    $category['childCount'],
                    $category['url'],
                    $depth - 1
                );
            }
        } else {
            unset($category['children']);
        }
    }

    /**
     * Return the sitemap list as an array
     * @param string|array $type Only return categories of given type
     * @param string $lang The language to get sitemap list for
     * @return array
     */
    public function getNavigationList($type = "all", string $lang = null): array
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $list = $this->filterCategoriesByTypes(
            $this->categoryRepository->getLinklistList(
                $type,
                $lang,
                $this->webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId
            )
        );

        /** @var CategoryDataFilter $filter */
        $filter = pluginApp(CategoryDataFilter::class);

        return $filter->applyResultFields(
            $list,
            ResultFieldTemplate::load(ResultFieldTemplate::TEMPLATE_CATEGORY_TREE)
        );
    }

    private function filterCategoriesByTypes($categoryList = [], $types = CategoryType::ALL)
    {
        if (is_string($types)) {
            if ($types === CategoryType::ALL) {
                $types = [
                    CategoryType::BLOG,
                    CategoryType::CONTAINER,
                    CategoryType::CONTENT,
                    CategoryType::ITEM
                ];
            } else {
                $types = [$types];
            }
        }

        $loggedIn = Utils::isContactLoggedIn();
        $result = array_filter(
            $categoryList,

            function ($category) use ($types, $loggedIn) {
                return in_array(
                        $category->type,
                        $types
                    ) && ($category->right !== 'customer' || $loggedIn || Utils::isAdminPreview());
            }
        );

        $result = array_map(
            function ($category) use ($types) {
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
     * @param int $catID The category Id to get the parents for or 0 to use current category
     * @param bool $bottomUp Set true to order result from bottom (deepest category) to top (= level 1)
     * @param bool $filterCategories Filter categories
     * @return array            The parents of the category
     */
    public function getHierarchy(int $catID = 0, bool $bottomUp = false, bool $filterCategories = false): array
    {
        if ($catID > 0) {
            $this->setCurrentCategoryID($catID);
        }

        $hierarchy = [];
        $loggedIn = Utils::isContactLoggedIn();

        /**
         * @var Category $category
         */
        foreach ($this->currentCategoryTree as $lvl => $category) {
            if ($filterCategories == false || $category->right === 'all' || $loggedIn || Utils::isAdminPreview()) {
                array_push($hierarchy, $category);
            } else {
                $hierarchy = [];
            }
        }

        if ($bottomUp === false) {
            $hierarchy = array_reverse($hierarchy);
        }

        if (count($this->currentItem)) {
            $lang = Utils::getLang();
            array_push($hierarchy, $this->currentItem['texts'][$lang]);
        }

        return $hierarchy;
    }

    public function isVisibleForWebstore($category, $webstoreId = null, $lang = null)
    {
        if (is_null($lang)) {
            $lang = Utils::getLang();
        }

        if (is_null($webstoreId)) {
            $webstoreId = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId;
        }


        return $category->clients->where('plenty_webstore_category_link_webstore_id', $webstoreId)->first(
            ) instanceof CategoryClient
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

    public function isHidden($id)
    {
        if (Utils::isAdminPreview()) {
            return false;
        }
        $isHidden = false;
        foreach ($this->getHierarchy($id) as $category) {
            if ($category->right === 'customer') {
                $isHidden = true;
                break;
            }
        }

        return $isHidden;
    }
}
