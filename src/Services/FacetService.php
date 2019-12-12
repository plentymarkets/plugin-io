<?php //strict

namespace IO\Services;

class FacetService
{
    /**
     * @param array $facets
     * @param string $type
     * @return bool
     */
    public function facetDataExists($facets, $type): bool
    {
        foreach ($facets as $facet) {
            if ($facet['type'] == $type[0]) {
                return true;
            }
        }
        return false;
    }
}
