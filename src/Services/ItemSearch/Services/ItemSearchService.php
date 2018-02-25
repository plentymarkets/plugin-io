<?php

namespace IO\Services\ItemSearch\Services;

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
            return $multiSearchFactory->getResults();

        }
        elseif ( $searches instanceof BaseSearchFactory )
        {
            $multiSearchFactory->addSearch( 'search', $searches );
            $results = $multiSearchFactory->getResults();

            return $results['search'];
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
}