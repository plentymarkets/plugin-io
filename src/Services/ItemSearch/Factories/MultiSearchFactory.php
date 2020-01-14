<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Contracts\MultiSearchFactoryContract;
use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\ItemSearch\Helper\FacetExtensionContainer;
use IO\Services\TemplateService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchMultiSearchRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class MultiSearchFactory
 *
 * Factory to build an elastic search multisearch request by collecting multiple search factory instances.
 *
 * @package IO\Services\ItemSearch\Factories
 * @deprecated use instead IO\Contracts\MultiSearchFactoryContract
 */
class MultiSearchFactory implements MultiSearchFactoryContract
{
    use Loggable;

    /** @var array */
    private $searches = [];

    /** @var array */
    private $extensions = [];

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
            /** @var DocumentSearch $search */
            $search = $searchBuilder->build();
            $search->setName( $resultName );

            $secondarySearches = [];

            foreach( $searchBuilder->getExtensions() as $i => $extensionContainer )
            {
                $extension = pluginApp($extensionContainer['class'], $extensionContainer['params']);
                // collect secondary searches required by registered extensions
                $secondarySearch = $extension->getSearch( $searchBuilder );
                if ( $secondarySearch !== null )
                {
                    $secondarySearch = $secondarySearch->build();
                    $secondarySearch->setName( $resultName . "__" . $i );
                    $secondarySearches[] = $secondarySearch;
                }
    
                $this->extensions[$resultName][] = $extension;
            }

            // primary search       = The search itself
            // secondary searches   = Additional searches required by registered extensions
            $this->searches[$resultName] = [
                'primary'   => $search,
                'secondary' => $secondarySearches
            ];
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
        /** @var VariationElasticSearchMultiSearchRepositoryContract $searchRepository */
        $searchRepository = pluginApp( VariationElasticSearchMultiSearchRepositoryContract::class );

        $primarySearchNames = [];
        foreach( $this->searches as $resultName => $searches )
        {
            // add all searches (primary & secondary)
            $searchRepository->addSearch( $searches['primary'] );

            // remember primary search names
            $primarySearchNames[] = $resultName;
            foreach( $searches['secondary'] as $secondarySearch )
            {
                $searchRepository->addSearch( $secondarySearch );
            }
        }

        // execute multisearch
        $rawResults = $searchRepository->execute();

        if ( count($this->searches) === 1 && count($this->searches[$primarySearchNames[0]]['secondary']) === 0 )
        {
            $tmp = $rawResults;
            $rawResults = [];
            $rawResults[$primarySearchNames[0]] = $tmp;
        }

        $results = [];

        foreach( $primarySearchNames as $searchName )
        {
            // get result of primary search
            $result = $rawResults[$searchName];

            if(array_key_exists('success', $result) && $result['success'] === false)
            {
                /** @var TemplateService $templateService */
                $templateService = pluginApp(TemplateService::class);
                $templateService->disableCacheForTemplate();

                $this->getLogger(__CLASS__)->error(
                    "IO::Debug.MultiSearchFactory_searchResultError",
                    [
                        "resultName" => $searchName,
                        "errorMessage" => $result['error']
                    ]
                );
            }

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
