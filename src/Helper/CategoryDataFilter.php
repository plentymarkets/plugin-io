<?php

namespace IO\Helper;

use Plenty\Modules\Category\Models\Category;

class CategoryDataFilter extends DataFilter
{
    protected $fields = [
        "id",
        "parentCategoryId",
        "level",
        "type",
        "linklist",
        "right",
        "sitemap",
        
        "details.categoryId",
        "details.lang",
        "details.name",
        "details.metaKeywords",
        "details.nameUrl",
        "details.metaTitle",
        "details.position",
        "details.updatedAt",
        "details.updatedBy",
        "details.itemListView",
        "details.singleItemView",
        "details.pageView",
        "details.fulltext",
        "details.metaRobots",
        "details.canonicalLink",
        "details.image",
        "details.imagePath",
        "details.image2",
        "details.image2Path",
        "details.plentyId",

        "itemCount.categoryId",
        "itemCount.webstoreId",
        "itemCount.lang",
        "itemCount.count",
        "itemCount.createdAt",
        "itemCount.updatedAt",
        "itemCount.variationCount",
        "itemCount.customerClassId",
    ];

    protected $listPrefixes = [
        "details",
        "itemCount"
    ];

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