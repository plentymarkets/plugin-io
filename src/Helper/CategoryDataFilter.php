<?php

namespace IO\Helper;

use Plenty\Modules\Category\Models\Category;

/**
 * Class CategoryDataFilter
 *
 * A data filter for categories.
 * Please refer to the parent class for more information.
 *
 * @package IO\Helper
 */
class CategoryDataFilter extends DataFilter
{
    /**
     * Filter a list of categories via result fields.
     * @param array $categoryList A list of categories.
     * @param array|null $resultFields Optional: A list of result fields for filtering.
     * @return array
     */
    public function applyResultFields( $categoryList, $resultFields = null )
    {
        $result = [];

        /** @var Category $category */
        foreach( $categoryList as $category )
        {
            if($category instanceof Category)
            {
                $categoryData = $category->toArray();
            }
            else
            {
                $categoryData = $category;
            }
            
            $resultData = null;

            if ( is_null( $resultFields ) )
            {
                $resultData = $categoryData;
            }
            else
            {
                $resultData = $this->getFilteredData( $categoryData, $resultFields );
            }

            if (is_array($categoryData['children']) && count($categoryData["children"]))
            {
                $resultData["children"] = $this->applyResultFields( $categoryData["children"], $resultFields );
            }

            $result[] = $resultData;
        }

        return $result;
    }
}
