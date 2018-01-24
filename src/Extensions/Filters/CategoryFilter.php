<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\ItemLoader\Services\LoadResultFields;
use Plenty\Modules\Category\Models\Category;

/**
 * Class CategoryFilter
 * @package IO\Extensions\Filters
 */
class CategoryFilter extends AbstractFilter
{
    use LoadResultFields;

    /**
     * CategoryFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFilters():array
    {
        return [
            'reduceCategory' => 'reduceCategory',
            'reduceCategoryTree' => 'reduceCategoryTree'
        ];
    }

    /**
     * @param array $categoryTree
     * @param string $resultFieldsTemplate
     * @return array
     */
    public function reduceCategoryTree(array $categoryTree, string $resultFieldsTemplate = ""):array
    {
        if ( strlen($resultFieldsTemplate))
        {
            $resultFields = $this->loadResultFields($resultFieldsTemplate);
        }
        else
        {
            return $categoryTree;
        }


        $reducedCategoryTree = [];

        foreach ($categoryTree as $category)
        {
            $reducedCategoryTree[] = $this->getReducedCategory($category, $resultFields);
        }

        return $reducedCategoryTree;
    }

    /**
     * @param Category $category
     * @param string|null $resultFieldsTemplate
     * @return array
     */
    public function reduceCategory(Category $category, string $resultFieldsTemplate = ""):array
    {
        if ( strlen($resultFieldsTemplate))
        {
            $resultFields = $this->loadResultFields($resultFieldsTemplate);
        }
        else
        {
            return $category;
        }

        return $this->getReducedCategory($category, $resultFields);
    }

    /**
     * @param Category $category
     * @param array $resultFields
     * @return array
     */
    private function getReducedCategory(Category $category, array $resultFields = []):array
    {
        $reducedCategory = [];

        // reduce all children recursive
        if ( count($category->children))
        {
            foreach ($category->children as $child)
            {
                $reducedCategory['children'][] = $this->getReducedCategory($child, $resultFields);
            }
        }

        foreach( $resultFields as $field )
        {
            if ( substr( $field, 0, strlen("details.")) === "details.")
            {
                $field = substr( $field, strlen("details." ) );
                if ( !array_key_exists( "details", $reducedCategory ) )
                {
                    $reducedCategory["details"] = [];
                }
                for($index = 0; $index <= count($category->details); $index++)
                {
                    if($category->details[$index]->$field != null)
                    {
                        $reducedCategory["details"][$index][$field] = $category->details[$index]->$field;
                    }
                }
            }
            else if ( substr( $field, 0, strlen("itemCount.")) === "itemCount.")
            {
                $field = substr( $field, strlen("itemCount." ) );
                if ( !array_key_exists( "itemCount", $reducedCategory ) )
                {
                    $reducedCategory["itemCount"] = [];
                }
                for($index = 0; $index <= count($category->itemCount); $index++)
                {
                    if ( $category->itemCount[$index]->$field != null)
                    {
                        $reducedCategory["itemCount"][$index][$field] = $category->itemCount[$index]->$field;
                    }
                }
            }
            else
            {
                if ( $category->$field != null)
                {
                    $reducedCategory[$field] = $category->$field;
                }
            }
        }

        return $reducedCategory;
    }
}
