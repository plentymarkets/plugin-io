<?php //strict

namespace IO\Services;

use IO\Services\Basket\Factories\BasketResultFactory;
use IO\Services\ItemSearch\Factories\Faker\FacetFaker;

/**
 * Service Class FakerService
 *
 * This service class contains functions related to faking test data for the shop builder.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class FakerService
{
    /**
     * Get a faked basket for shopbuilder basket templates
     * @return array
     */
    public function getBasket()
    {
        /** @var BasketResultFactory $basketResultFactory */
        $basketResultFactory = pluginApp(BasketResultFactory::class);
        return $basketResultFactory->fillBasketResult();
    }

    /**
     * Get a faked facet result for shopbuilder category templates
     * @return array
     */
    public function getFacets()
    {
        /** @var FacetFaker $facetFaker */
        $facetFaker = pluginApp(FacetFaker::class);
        $facetResult = $facetFaker->fill([]);

        return $facetResult;
    }

    /**
     * Get a faked selected facets array for shopbuilder category templates
     * @param array $facets Available facets
     * @return array
     */
    public function getSelectedFacetIds($facets)
    {
        $selectedFacetIds = [];
        foreach ($facets[0]['values'] as $facetValue) {
            $selectedFacetIds[] = $facetValue['id'];
        }

        return $selectedFacetIds;
    }
}
