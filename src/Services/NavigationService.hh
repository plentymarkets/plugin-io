<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;

use LayoutCore\Constants\CategoryType;
use LayoutCore\Constants\Language;

class NavigationService {

    private CategoryRepositoryContract $categoryRepository;

    public function __construct( CategoryRepositoryContract $categoryRepository ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Returns sitemap tree as array
     * @param string $type  Only return categories of given type
     * @param string $lang  The language to get sitemap tree for
     * @return array<string, mixed>
     */
    public function getNavigationTree( string $type = "all", string $lang = "de"):array<Category> {
        return $this->categoryRepository->getSitemapTree( $type, $lang );
    }

    /**
     * Returns sitemap list as array
     * @param string $type  Only return categories of given type
     * @param string $lang  The language to get sitemap list for
     * @return array<string, mixed>
     */
    public function getNavigationList( string $type = "all", string $lang = "de"):array<string, mixed> {
        return $this->toArray( $this->categoryRepository->getSitemapList( $type, $lang ) );
    }

    // FIXME arrays of objects are not transformed to arrays of native types before passing to twig templates.
    private function toArray( array<Category> $categories ):array<string, mixed>
    {
        $result = array();
        foreach( $categories as $category )
        {
            array_push($result, $category->toArray() );
        }

        return $result;
    }

}
