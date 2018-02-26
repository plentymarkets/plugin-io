<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

/**
 * Class FacetFilterExtension
 *
 * Filter facets by configured minimum hit count.
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class FacetFilterExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return null;
    }

    /**
     * @inheritdoc
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