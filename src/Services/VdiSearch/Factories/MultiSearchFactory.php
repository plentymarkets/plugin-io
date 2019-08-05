<?php

namespace IO\Services\VdiSearch\Factories;

use IO\Contracts\MultiSearchFactoryContract;
use IO\Helper\VDIToElasticSearchMapper;
use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\ItemSearch\Helper\FacetExtensionContainer;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;

/**
 * Class MultiSearchFactory
 *
 * Factory to build an elastic search multisearch request by collecting multiple search factory instances.
 *
 * @package IO\Services\ItemSearch\Factories
 */
class MultiSearchFactory implements MultiSearchFactoryContract
{
    /** @var array */
    private $searches = [];

    /** @var array */
    private $extensions = [];
    
    private $resultFields = [];

    /**
     * Get all registered searches
     *
     * @return array
     */
    public function getSearches()
    {
        return $this->searches;
    }

    /**
     * Get all registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Add a search factory instance to be included in current mutlisearch request.
     *
     * @param string            $resultName     An unique name for the search. Results of this search will be accessible by this key.
     * @param BaseSearchFactory $searchBuilder  A search factory
     *
     * @return MultiSearchFactory
     */
    public function addSearch( $resultName, $searchBuilder )
    {
        if ( !array_key_exists( $resultName, $this->searches ) )
        {
            $this->resultFields[$resultName] = $searchBuilder->getResultFields();
            /** @var DocumentSearch $search */
            $search = $searchBuilder->build();

            $secondarySearches = [];

            foreach( $searchBuilder->getExtensions() as $i => $extension )
            {
                // collect secondary searches required by registered extensions
                $secondarySearch = $extension->getSearch( $searchBuilder );
                if ( $secondarySearch !== null )
                {
                    //$secondarySearch->setName( $resultName . "__" . $i );
                    $secondarySearches[$resultName . "__" . $i] = $secondarySearch;
                    $this->resultFields[$resultName . "__" . $i] = $secondarySearch->getResultFields();
                }
            }

            // primary search       = The search itself
            // secondary searches   = Additional searches required by registered extensions
            $this->searches[$resultName] = [
                'primary'   => $search,
                'secondary' => $secondarySearches
            ];

            $this->extensions[$resultName] = $searchBuilder->getExtensions();
        }
        return $this;
    }

    /**
     * Execute the multisearch and collect results.
     *
     * @return array
     */
    public function getResults()
    {
        $vdiContexts = [];
        $primarySearchNames = [];
        
        foreach( $this->searches as $resultName => $searches )
        {
            $vdiContexts[$resultName] = $searches['primary'];
              // remember primary search names
            $primarySearchNames[] = $resultName;
            foreach( $searches['secondary'] as $secondaryResultName => $secondarySearch )
            {
                $vdiContexts[$secondaryResultName] = $secondarySearch;
            }
        }

        /** @var VariationDataInterfaceContract $searchRepository */
        $searchRepository = app(VariationDataInterfaceContract::class);
        $rawResults = $searchRepository->getMultipleResults($vdiContexts);
        
        /*$results = [];
        // execute multisearch
        // $rawResults = $searchRepository->execute();

        if ( count($this->searches) === 1 && count($this->searches[$primarySearchNames[0]]['secondary']) === 0 )
        {
            $tmp = $rawResults;
            $rawResults = [];
            $rawResults[$primarySearchNames[0]] = $tmp;
        }*/

//        $results = [];

        if(!is_null($rawResults) && count($rawResults))
        {
            foreach($rawResults as $key => $rawResult)
            {
                /**
                 * @var VDIToElasticSearchMapper $mappingHelper
                 */
                $mappingHelper = pluginApp(VDIToElasticSearchMapper::class);
                $mappedData = $mappingHelper->map($rawResult, $this->resultFields[$key]);
                
                $rawResults[$key] = $mappedData;
            }
        }
        else
        {
            $rawResults = [];
        }

        foreach( $primarySearchNames as $searchName )
        {
            // get result of primary search
            $result = $rawResults[$searchName];

            // apply extensions
            foreach( $this->extensions[$searchName] as $i => $extension )
            {
                if ( $extension instanceof ItemSearchExtension )
                {
                    $result = $extension->transformResult( $result, $rawResults[$searchName."__".$i] );
                }
            }

            if ( array_key_exists( 'facets', $result ) )
            {
                $results[$searchName] = $result['facets'];
            }
            else
            {
                $results[$searchName] = $result;
            }
        }

        /** @var FacetExtensionContainer $facetExtensionContainer */
        $facetExtensionContainer = pluginApp(FacetExtensionContainer::class);
        $facetExtensions = $facetExtensionContainer->getFacetExtensions();

        if(isset($results['facets']) && count($facetExtensions))
        {
            foreach($results as $searchName => $searchData)
            {
                foreach ($facetExtensions as $facetExtension)
                {
                    $aggregationName = $facetExtension->getAggregation()->getName();
                    if(isset($searchData[$aggregationName]))
                    {
                        $facetData = $facetExtension->mergeIntoFacetsList($searchData[$aggregationName]);
                        if(count($facetData))
                        {
                            array_unshift($results['facets'], $facetData);
                        }
                    }
                }
            }
        }

        return $results;
    }
}
