<?php

namespace IO\Services;

use IO\Constants\CategoryType;
use IO\Guards\AuthGuard;
use IO\Helper\CategoryDataFilter;
use IO\Helper\MemoryCache;
use IO\Helper\Utils;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Webshop\Category\Contracts\CategoryRepositoryContract as WebshopCategoryRepositoryContract;
use Plenty\Modules\Category\Models\CategoryClient;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;

/**
 * Class CategoryService
 *
 * This service class contains methods related to Category models.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class CategoryService
{
    use MemoryCache;
    use LoadResultFields;

    /**
     * @var CategoryRepositoryContract This repository is used to manipulate Category models.
     */
    private $categoryRepository;

    /**
     * @var WebshopCategoryRepositoryContract This repository is used to manipulate Category models.
     */
    private $webshopCategoryRepository;
    /**
     * @var WebstoreConfigurationRepositoryContract This repository is used to read the webstore configuration.
     */
    private $webstoreConfigurationRepository;

    /**
     * @var ContactRepositoryContract $contactRepository This repository is used to manipulate Contact models.
     */
    private $contactRepository;

    /**
     * @var UrlBuilderRepositoryContract Repository to build category urls
     */
    private $urlBuilderRepository;

    // is set from controllers
    /**
     * @var Category The current Category model.
     */
    private $currentCategory = null;

    /**
     * @var array The full branch of the category with parents, e.g. Plants -> Edible -> Tomatoes.
     */
    private $currentCategoryTree = [];

    /**
     * @var AuthGuard Guard class to check for login status.
     */
    private $authGuard;

    /**
     * @var array An item.
     */
    private $currentItem = [];

    /**
     * @var null|int The current webstore id.
     */
    private $webstoreId = null;

    /**
     * CategoryService constructor.
     *
     * @param CategoryRepositoryContract $categoryRepository This repository is used to manipulate Category models.
     * @param WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository This repository is used to read the webstore configuration.
     * @param AuthGuard $authGuard Guard class to check for login status.
     * @param ContactRepositoryContract $contactRepository This repository is used to manipulate Contact models.
     * @param UrlBuilderRepositoryContract $urlBuilderRepository Repository to build category urls
     */
    public function __construct(
        CategoryRepositoryContract $categoryRepository,
        WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository,
        AuthGuard $authGuard,
        ContactRepositoryContract $contactRepository,
        UrlBuilderRepositoryContract $urlBuilderRepository,
        WebshopCategoryRepositoryContract $webshopCategoryRepositoryContract
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->webstoreConfigurationRepository = $webstoreConfigurationRepository;
        $this->authGuard = $authGuard;
        $this->webstoreId = Utils::getWebstoreId();
        $this->contactRepository = $contactRepository;
        $this->urlBuilderRepository = $urlBuilderRepository;
        $this->webshopCategoryRepository = $webshopCategoryRepositoryContract;
    }

    /**
     * Set the current category by id.
     *
     * @param int $catID The id of the current category
     */
    public function setCurrentCategoryID(int $catID = 0)
    {
        $this->setCurrentCategory(
            $this->webshopCategoryRepository->get($catID, Utils::getLang(), $this->webstoreId)
        );
    }

    /**
     * Set the current category by id.
     *
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

            if($cat->parentCategoryId != null){
                $cat = $this->webshopCategoryRepository->get($cat->parentCategoryId, $lang, $this->webstoreId);
            } else {
                $cat = null;
            }
        }
    }

    /**
     * Gets the current category or null if not set
     *
     * @return Category|null
     */
    public function getCurrentCategory()
    {
        return $this->currentCategory;
    }

    /**
     * Get a category by id
     *
     * @param int $catID The category id
     * @param string|null $lang The language to get the category (ISO-639-1)
     * @return Category
     */
    public function get($catID = 0, $lang = null)
    {
        if(is_null($catID) || strlen($catID) == 0) {
            return null;
        }

        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $webstoreId = $this->webstoreId;
        $category = $this->fromMemoryCache(
            "category.$catID.$lang",
            function () use ($catID, $lang, $webstoreId) {
                $category = $this->webshopCategoryRepository->get((int)$catID, $lang, $webstoreId);
                return $category;
            }
        );

        return $category;
    }

    /**
     * Gets a category by id and plentyId
     *
     * @param int $catID The category id
     * @param string|null $lang The language to get the category (ISO-639-1)
     * @param int|null $plentyId The plentyId to which the category has to be linked
     * @return Category|null
     */
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
     * Get child categories of a given category referenced by id
     *
     * @param int $categoryId The category id, which children should be fetched
     * @param string|null $lang The language in ISO-639-1 format
     * @return Category[]|null
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

    /**
     * Gets the children of the given categoryId or the current category if no categoryId is given.
     *
     * @param int|null $categoryId
     * @return array|mixed|null
     */
    public function getCurrentCategoryChildren($categoryId = null)
    {
        $category = $this->getCurrentCategory();

        if (is_null($category)) {
            $category = $this->get((int)$categoryId);
            if (is_null($category)) {
                return [];
            }
        }

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);

        $tree = $this->getNavigationTree(
            $templateConfigService->get('header.showCategoryTypes'),
            Utils::getLang(),
            $category->level + 1,
            $this->contactRepository->getContactClassId()
        );

        $foundCategory = $this->findInCategoryTree($tree, $category->branch->toArray());
        if (isset($foundCategory) && isset($foundCategory['children'])) {
            return $foundCategory['children'];
        }

        return null;
    }

    /**
     * Recursively iterate through category tree and return the category with the given id.
     *
     * @param object $categoryTree A tree containing all categories
     * @param array $branch The current branch of the categoryTree
     * @param int $level The current depth of the recursion
     * @return mixed|null
     */
    protected function findInCategoryTree($categoryTree, $branch = [], $level = 1)
    {
        $result = null;
        $branchKey = 'category' . $level . 'Id';
        $categoryId = $branch['categoryId'];

        foreach ($categoryTree as $category) {
            if ($category['id'] !== $branch[$branchKey]) {
                continue;
            } elseif ($category['id'] == $categoryId) {
                $result = $category;
                break;
            } elseif (is_array($category['children']) && count($category['children'])) {
                $result = $this->findInCategoryTree($category['children'], $branch, $level + 1);
                break;
            }
        }

        return $result;
    }

    /**
     * Return the URL for a given category.
     *
     * @param Category $category The category to get the URL for
     * @param string|null $lang The language to get the URL for (ISO-639-1)
     * @param int|null $webstoreId Id of the webstore to get the category url for
     * @return string|null
     */
    public function getURL($category, $lang = null, $webstoreId = null)
    {
        if (!$category instanceof Category || $category->details->first() === null) {
            return null;
        }

        return $this->getURLById($category->id, $lang, $webstoreId);
    }

    /**
     * Return the url for a given category id.
     *
     * @param int $categoryId Id of category to fetch
     * @param string|null $lang Language in format ISO-639-1
     * @param int|null $webstoreId Id of the webstore to get the category url for
     * @return string|null
     */
    public function getURLById($categoryId, $lang = null, $webstoreId = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        if (is_null($webstoreId)) {
            $webstoreId = Utils::getWebstoreId();
        }
        $defaultLanguage = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;

        return  $this->fromMemoryCache(
            "categoryUrl.$categoryId.$lang.$webstoreId",
            function () use ($categoryId, $lang, $webstoreId, $defaultLanguage) {
                $categoryURL = $this->urlBuilderRepository->buildCategoryUrl($categoryId, $lang, $webstoreId);
                return $categoryURL->toRelativeUrl($lang !== $defaultLanguage);
            }
        );
    }

    /**
     * Get CategoryDetails of the given category for a given language
     *
     * @param Category $category The category model
     * @param string $lang The language in format ISO-639-1
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
     *
     * @param Category|array $category The category to check
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
     *
     * @param Category|array $category The category to check
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
     *
     * @param Category|array|null $category The category to check
     * @return bool
     */
    public function isActive($category = null): bool
    {
        return $category !== null && ($this->isCurrent($category) || $this->isOpen($category));
    }

    /**
     * Get items for the given or current category
     *
     * @param Category|null $category The category of which you want items of (null means currentCategory)
     * @param array $params The parameters for the repository
     * @param int $page The desired page, always >0
     * @return array
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
     *
     * @param string|array|null $type Only return categories of given types
     * @param string|null $lang The language to get sitemap tree for
     * @param int|null $maxLevel The deepest category level to load
     * @param int $customerClassId The customer class id to get tree
     * @return array
     * @throws \Exception
     */
    public function getNavigationTree(
        $type = null,
        string $lang = null,
        int $maxLevel = 2,
        int $customerClassId = 0
    ): array
    {
        if (is_array($type) && count($type) === 0) {
            return [];
        }

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

    /**
     * Builds a partial tree of starting from the given category id
     *
     * @param int $categoryId The category id
     * @param string $type The type of category, see /IO/Constants/CategoryType
     * @return array
     * @throws \Exception
     */
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

    /**
     * Return the sitemap list as an array
     *
     * @param string|array $type Only return categories of given type, see /IO/Constants/CategoryType
     * @param string|null $lang The language to get sitemap list for (ISO-639-1)
     * @return array
     * @throws \Exception
     */
    public function getNavigationList($type = CategoryType::ALL, string $lang = null): array
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

    /**
     * Returns a list of all parent categories including given category.
     *
     * @param int $catID The category id to get the parents for or 0 to use current category
     * @param bool $bottomUp Set true to order result from bottom (deepest category) to top (= level 1)
     * @param bool $filterCategories Filter categories
     * @param bool $restoreOldValues Restore old category data and category tree after the method call.
     * @return array The parents of the category
     */
    public function getHierarchy(int $catID = 0, bool $bottomUp = false, bool $filterCategories = false, $restoreOldValues = false): array
    {
        if ($catID > 0) {

            if ($restoreOldValues) {
                $oldCategory = $this->currentCategory;
                $oldCategoryTree = $this->currentCategoryTree;
            }
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

        if (is_array($this->currentItem) && count($this->currentItem)) {
            $lang = Utils::getLang();
            array_push($hierarchy, $this->currentItem['texts'][$lang]);
        }

        if ($restoreOldValues) {
             $this->currentCategory = $oldCategory;
             $this->currentCategoryTree = $oldCategoryTree;
        }

        return $hierarchy;
    }

    /**
     * Check if a category is visible for a webstore.
     *
     * @param Category $category Category model to check
     * @param int|null $webstoreId WebstoreId for filtering result (not plentyId)
     * @param string|null $lang Language for filtering result (ISO-639-1)
     * @return bool
     */
    public function isVisibleForWebstore($category, $webstoreId = null, $lang = null)
    {
        if (is_null($lang)) {
            $lang = Utils::getLang();
        }

        if (is_null($webstoreId)) {
            $webstoreId = $this->webstoreConfigurationRepository->getWebstoreConfiguration()->webstoreId;
        }


        return $category->clients->where('plenty_webstore_category_link_webstore_id', $webstoreId)->first() instanceof CategoryClient
            && $category->details->where('lang', $lang)->first() instanceof CategoryDetails;
    }

    /**
     * Setter for current item array.
     *
     * @param array $item An item.
     */
    public function setCurrentItem($item)
    {
        $this->currentItem = $item;
    }

    /**
     * Getter for current item array.
     *
     * @return array
     */
    public function getCurrentItem()
    {
        return $this->currentItem;
    }

    /**
     * Check wether a category is hidden or not
     *
     * @param int $categoryId The id of the category to check
     * @return bool
     */
    public function isHidden($categoryId)
    {
        if (Utils::isAdminPreview()) {
            return false;
        }
        $isHidden = false;
        foreach ($this->getHierarchy($categoryId, false, false, true) as $category) {
            if ($category->right === 'customer') {
                $isHidden = true;
                break;
            }
        }

        return $isHidden;
    }

    private function filterVisibleCategories($categoryList = [])
    {
        $result = array_filter(
            $categoryList ?? [],
            function ($category) {
                return $category['right'] !== 'customer';
            }
        );

        $result = array_map(
            function ($category) {
                $category['children'] = $this->filterVisibleCategories($category['children']);
                return $category;
            },
            $result
        );

        return $result;
    }

    private function filterBranchEntries($tree, $branch = [], $level = 1, $urlPrefix = '')
    {
        $branchKey = "category" . $level . "Id";
        $isCurrentLevel = $branch[$branchKey] === $branch["categoryId"];
        $result = [];
        $siblingCount = count($tree ?? []);

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
            } elseif ($isInBranch && $isCurrentLevel) {
                $this->appendBranchFields($category, $siblingCount, $urlPrefix, 2);
                $result[] = $category;
            } elseif (!$isInBranch && $isCurrentLevel) {
                $this->appendBranchFields($category, $siblingCount, $urlPrefix, 0);
                $result[] = $category;
            }
        }

        return $result;
    }

    private function appendBranchFields(&$category, $siblingCount = 1, $urlPrefix = '', $depth = 6)
    {
        // Filter children not having texts in current language
        $category['children'] = array_filter(
            $category['children'] ?? [],
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
}
