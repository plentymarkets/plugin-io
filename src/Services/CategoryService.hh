<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Category\Contracts\CategoryRepository;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Repositories\Models\PaginatedResult;

use LayoutCore\Constants\CategoryType;
use LayoutCore\Services\ItemService;
use LayoutCore\Helper\CategoryMap;
use LayoutCore\Helper\CategoryKey;

class CategoryService
{
    private CategoryRepository $category;
    private ItemService $item;
    private CategoryMap $categoryMap;

    // is set from controllers
    private ?Category $currentCategory = null;
    private array<int, Category> $currentCategoryTree = array();

    public function __construct( CategoryRepository $category, ItemService $item, CategoryMap $categoryMap )
    {
        $this->category = $category;
        $this->item = $item;
        $this->categoryMap = $categoryMap;
    }

    /**
     * Set the current category by id.
     * @param int $catID The id of the current category
     * @return void
     */
    public function setCurrentCategoryID( int $catID ):void
    {
        $this->setCurrentCategory(
            $this->category->get( $catID )
        );
    }

    /**
     * Set the current category by id.
     * @param Category $cat The current category
     * @return void
     */
    public function setCurrentCategory( ?Category $cat ):void
    {
        $this->currentCategory = null;
        $this->currentCategoryTree = array();

        if( $cat === null )
        {
            return;
        }

        // get parent/open categories
        $this->currentCategory = $cat;
        while( $cat !== null )
        {
            $this->currentCategoryTree[ $cat->level ] = $cat;
            $cat = $this->category->get( $cat->parentCategoryId );
        }
    }

    /**
     * Get a category by id
     * @param int       $catID The category ID
     * @param string    $lang The language to get the category
     * @return Category
     */
     public function get( int $catID, string $lang = "de" ):?Category
     {
         return $this->category->get( $catID, $lang );
     }

    /**
     * Returns the URL for a given category id.
     * @param Category  $category   the category to get the URL for
     * @param string    $lang       the language to get the URL for
     * @return string
     */
    public function getURL( Category $category, string $lang = "de" ):string
    {
        if( $category->details[0] === null )
        {
            return "ERR";
        }
        return "/" . $this->category->getUrl( $category->id, $lang );
    }

    /**
     * Checks if a category is referenced by current route
     * @param int $catID The ID for the category to check
     * @return bool
     */
    public function isCurrent( Category $category ):bool
    {
        if( $this->currentCategory === null )
        {
            return false;
        }
        return $this->currentCategory->id === $category->id;
    }

    /**
     * Checks if any child of a category is referenced by current route
     * @param int $catID The ID for the category to check
     * @return bool
     */
    public function isOpen( Category $category ):bool
    {
        if( $this->currentCategory === null )
        {
            return false;
        }

        foreach( $this->currentCategoryTree as $lvl => $categoryBranch )
        {
            if( $categoryBranch->id === $category->id )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a category or any of its children is referenced by current route
     * @param int $catID The ID for the category to check
     * @return bool
     */
    public function isActive( Category $category ):bool
    {
        return ($this->isCurrent( $category ) || $this->isOpen( $category ));
    }

    public function isHome():bool
    {
        return $this->currentCategory !== null && $this->currentCategory->id == $this->categoryMap->getID( CategoryKey::HOME );
    }

    public function getItems( ?Category $category = $this->currentCategory, int $defaultItemPerPage = 0, int $variationShowType = 1 ):?PaginatedResult
    {
        if( $category == null )
        {
            return null;
        }
        return $this->item->getItemForCategory( $category->id, $variationShowType );
    }

    public function getCategoryTreeAsList( ?int $catID ): array<string, string>
    {
        $categoryTree = array();

        if ($catID !== null) {
            $this->setCurrentCategoryID($catID);
        }

        for ($i = 0; $i <= count($this->currentCategoryTree); $i++) {
            if ($this->currentCategoryTree[$i] !== null) {
                $details = $this->currentCategoryTree[$i]->details[0];
                $categoryTree[$details->name] = $details->nameUrl;
            }
        }

        return $categoryTree;
    }
}
