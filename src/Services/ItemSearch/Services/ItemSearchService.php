<?php

namespace IO\Services\ItemSearch\Services;

use IO\Helper\DefaultSearchResult;
use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\ItemSearch\Factories\MultiSearchFactory;

/**
 * Class ItemSearchService
 *
 * Execute elastic search requests.
 *
 * @package IO\Services\ItemSearch\Services
 */
class ItemSearchService
{
    /**
     * Get search results for multiple search requests.
     *
     * @param array     $searches   Map of search factories to execute.
     *
     * @return array                Results of multisearch request. Keys will be used from input search map.
     */
    public function getResults( $searches )
    {
        /** @var MultiSearchFactory $multiSearchFactory */
        $multiSearchFactory = pluginApp( MultiSearchFactory::class );

        if ( is_array( $searches ) )
        {
            foreach( $searches as $resultName => $search )
            {
                $multiSearchFactory->addSearch( $resultName, $search );
            }
            $results = $multiSearchFactory->getResults();

            foreach( $results as $resultName => $result )
            {
                $results[$resultName] = $this->normalizeResult( $result );
            }

            return $results;

        }
        elseif ( $searches instanceof BaseSearchFactory )
        {
            $multiSearchFactory->addSearch( 'search', $searches );
            $results = $multiSearchFactory->getResults();

            return $this->normalizeResult( $results['search'] );
        }


    }

    /**
     * Get result of a single search factory;
     *
     * @param BaseSearchFactory $searchFactory    The factory to get results for.
     *
     * @return array
     */
    public function getResult( $searchFactory )
    {
        return $this->getResults([$searchFactory]);
    }

    private function normalizeResult($result)
    {
        if( count($result['documents']) )
        {
            foreach($result['documents'] as $key => $variation)
            {
                $result['documents'][$key]['data'] = DefaultSearchResult::merge( $variation['data'] );
            }
        }

        return $result;
    }
}