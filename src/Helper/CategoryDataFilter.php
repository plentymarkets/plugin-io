<?php

namespace IO\Helper;

use Plenty\Modules\Category\Models\Category;

class CategoryDataFilter extends DataFilter
{
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