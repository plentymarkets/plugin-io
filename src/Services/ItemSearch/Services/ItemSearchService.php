<?php

namespace IO\Services\ItemSearch\Services;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\ItemSearch\Factories\MultiSearchFactory;

class ItemSearchService
{
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

    public function getResult( $searchFactory )
    {
        return $this->getResults([$searchFactory]);
    }
}