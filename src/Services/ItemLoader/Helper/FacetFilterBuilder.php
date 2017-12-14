<?php

namespace IO\Services\ItemLoader\Helper;

use IO\Builder\Facet\FacetBuilder;
use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Item\Search\Helper\SearchHelper;
use Plenty\Plugin\Application;

class FacetFilterBuilder implements FilterBuilder
{
    public function getFilters($options):array
    {
        $plentyId = pluginApp(Application::class)->getPlentyId();
        $lang = pluginApp(SessionStorageService::class)->getLang();
    
        $filters = $this->getFacetValues($options);
        $facetValues = $filters['facetValues'];
        $activeFilters = $filters['activeFilters'];
    
        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp(SearchHelper::class, [$facetValues, $plentyId, 'item', $lang]);
        $facetFilter = $searchHelper->getFacetFilter();
        
        $filters = [$facetFilter];
        
        if(!empty($activeFilters))
        {
            /** @var FacetExtensionContainer $facetExtensionContainer */
            $facetExtensionContainer = pluginApp(FacetExtensionContainer::class);
            
            foreach ($facetExtensionContainer->getFacetExtensions() as $facetExtension)
            {
                if ($facetExtension instanceof FacetExtension)
                {
                    $filter = $facetExtension->extractFilterParams($activeFilters);
                    if(!is_null($filter))
                    {
                        $filters[] = $filter;
                    }
                }
            }
        }
        
        return $filters;
    }
    
    private function getFacetValues($options)
    {
        if (array_key_exists('facets', $options) && count($options['facets'])) {
            $facetValues   = FacetBuilder::buildFacetValues($options['facets']);
            $activeFilters = explode(',', $options['facets']);
        } else {
            /**
             * @var Request $request
             */
            $request       = pluginApp(Request::class);
            $facetValues   = FacetBuilder::buildFacetValues($request->get('facets', ''));
            $activeFilters = explode(',', $request->get('facets', ''));
        }
        
        return [
            'facetValues' => $facetValues,
            'activeFilters' => $activeFilters
        ];
    }
}