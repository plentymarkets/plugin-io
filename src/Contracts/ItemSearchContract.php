<?php

namespace IO\Contracts;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\VdiSearch\Factories\BaseSearchFactory as VdiBaseSearchFactory;

interface ItemSearchContract
{
    /**
     * Get search results for multiple search requests.
     *
     * @param array     $searches   Map of search factories to execute.
     *
     * @return array    Results of multisearch request. Keys will be used from input search map.
     */
    public function getResults( $searches );
}
