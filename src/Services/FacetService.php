<?php //strict

namespace IO\Services;

/**
 * Service Class FacetService
 *
 * This service class contains functions related to facets.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class FacetService
{
    /**
     * Check if facet data exists
     * @param array $facets All available facets
     * @param array $type Allowed facet type
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
