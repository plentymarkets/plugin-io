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
            $categoryData = $category->toArray();
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