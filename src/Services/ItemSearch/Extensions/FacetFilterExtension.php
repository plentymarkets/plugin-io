<?php

namespace IO\Services\ItemSearch\Extensions;

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
        if ($baseResult['facets']) {
            $facets = $baseResult['facets'];
            $filteredFacets = [];

            foreach ($facets as $facet) {
                $hits = array_filter($facet['values'], function ($value) use ($facet) {
                    return (int)$value['count'] >= (int)$facet['minHitCount'];
                });

                if (count($hits) || $facet['type'] == 'price') {
                    $facet['values'] = $hits;
                    $facet['values'] = array_slice($facet['values'], 0, (int)$facet['maxResultCount']);
                    $filteredFacets[] = $facet;
                }
            }
            $baseResult['facets'] = $this->sortFacets($filteredFacets);
        }

        return $baseResult;
    }

    private function sortFacets($facets)
    {
        usort($facets,
            function ($facetA, $facetB) {
                return ($facetA['position'] <=> $facetB['position']);
            });

        foreach ($facets as $i => $facet) {
            usort($facets[$i]['values'],
                function ($valueA, $valueB) {
                    return ($valueA['position'] <=> $valueB['position']);
                });
        }

        return $facets;
    }
}