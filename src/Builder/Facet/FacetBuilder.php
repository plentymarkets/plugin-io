<?php

namespace IO\Builder\Facet;

class FacetBuilder
{
    public static function buildFacetValues($facetValueString)
    {
        $facetValues = [];

        if (strlen($facetValueString)) {
            $facetValuesList = explode(',', $facetValueString);
            if (count($facetValuesList)) {
                foreach ($facetValuesList as $k => $v) {
                    if ((int)$v) {
                        $facetValues[$k] = (int)$v;
                    }
                }
            }
        }

        return $facetValues;
    }
}