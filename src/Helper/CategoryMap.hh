<?hh //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Events\Dispatcher;

enum CategoryKey:string
{
    HOME                = "home";
    PAGE_NOT_FOUND      = "page_not_found";
    ITEM_NOT_FOUND      = "item_not_found";
}

class CategoryMap
{
    private array<CategoryKey, int> $categoryMap = array();

    public function __construct( Dispatcher $event )
    {
        $event->fire(
            "init.categories",
            array( $this )
        );
    }

    public function setCategoryMap( array<CategoryKey, int> $categoryMap ):void
    {
        $this->categoryMap = $categoryMap;
    }

    public function getID( CategoryKey $key ):int
    {
        return $this->categoryMap[$key];
    }
}
