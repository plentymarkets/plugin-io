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
     * Filter a list of categories via resultfields
     * @param array $categoryList A list of categories
     * @param array|null $resultFields Optional: A list of resultfields for filtering
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

            if ( count( $categoryData["children"] ) )
            {
                $resultData["children"] = $this->applyResultFields( $categoryData["children"], $resultFields );
            }

            $result[] = $resultData;
        }

        return $result;
    }
}
