<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

interface ItemSearchExtension
{
    /**
     * Get an additional elastic search request to load item data
     * required for this extension when extending the original item result.
     *
     * @param  BaseSearchFactory $parentSearchBuilder    Search factory of the parent elastic search request. Can be used to inherit active filters, mutators or pagination settings
     * @return DocumentSearch
     */
    public function getSearch( $parentSearchBuilder );

    /**
     * Extend the original item search result with custom data.
     * Should return the original search result after applying any transformations.
     *
     * @param   array $baseResult       Search result of the parent elastic search request.
     * @param   array $extensionResult  Search result of the additional search request defined by the extension itself.
     * @return  mixed
     */
    public function transformResult( $baseResult, $extensionResult );

}