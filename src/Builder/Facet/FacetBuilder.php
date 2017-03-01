<?php

namespace IO\Builder\Facet;

class FacetBuilder
{
    public static function buildFacetValues($requestArray)
    {
        $facetValues = [];
        $facetIdentifier = 'f_';
        
        foreach($requestArray as $key => $value)
        {
            $filterPos = strpos($key, $facetIdentifier);
            if($filterPos !== false && $filterPos === 0)
            {
                $values = explode(',', $value);
                if(count($values))
                {
                    foreach($values as $k => $v)
                    {
                        $values[$k] = (int)$v;
                    }
                    $facetValues = array_merge($facetValues, $values);
                }
            }
        }
        
        return $facetValues;
    }
}