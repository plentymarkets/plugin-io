<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

class FacetFilterExtension implements ItemSearchExtension
{
    /**
     * @param BaseSearchFactory $parentSearchBuilder
     * @return DocumentSearch
     */
    public function getSearch($parentSearchBuilder)
    {
        return null;
    }

    /**
     * @param $baseResult
     * @param $extensionResult
     * @return mixed
     */
    public function transformResult($baseResult, $extensionResult)
    {
        if ( $baseResult['facets'] )
        {
            $facets = $baseResult['facets'];
            $filteredFacets = [];

            foreach( $facets as $facet )
            {
                if ( (int) $facet['count'] >= (int) $facet['minHitCount'] )
                {

                    $facet['values'] = array_slice( $facet['values'], 0, (int) $facet['maxResultCount'] );
                    $filteredFacets[] = $facet;
                }
            }
            $baseResult['facets'] = $filteredFacets;
        }

        return $baseResult;
    }
}