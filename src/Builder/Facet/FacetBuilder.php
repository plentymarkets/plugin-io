<?php

namespace IO\Builder\Facet;

class FacetBuilder
{
    public static function buildFacetValues($facetValueString)
    {
        $facetValues = [];
        
        if(strlen($facetValueString))
        {
            $facetValues = explode(',', $facetValueString);
            if(count($facetValues))
            {
                foreach($facetValues as $k => $v)
                {
                    $facetValues[$k] = (int)$v;
                }
            }
        }
        
        return $facetValues;
    }
}