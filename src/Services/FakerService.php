<?php //strict

namespace IO\Services;

use IO\Services\Basket\Factories\BasketResultFactory;
use IO\Services\ItemSearch\Factories\Faker\FacetFaker;

class FakerService
{
    /**
     * Get a faked basket for shopbuilder basket templates
     */
    public function getBasket()
    {
        /** @var BasketResultFactory $basketResultFactory */
        $basketResultFactory = pluginApp(BasketResultFactory::class);
        return $basketResultFactory->fillBasketResult();
    }
    
    public function getFacets()
    {
        /** @var FacetFaker $facetFaker */
        $facetFaker = pluginApp(FacetFaker::class);
        $facetResult = $facetFaker->fill([]);
        
        return [
            $facetResult[0]
        ];
    }
    
    public function getSelectedFacetIds($facets)
    {
        $selectedFacetIds = [];
        foreach($facets[0]['values'] as $facetValue)
        {
            $selectedFacetIds[] = $facetValue['id'];
        }
        
        return $selectedFacetIds;
    }
}
