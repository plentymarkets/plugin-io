<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

interface ItemSearchExtension
{
    /**
     * @param BaseSearchFactory $parentSearchBuilder
     * @return DocumentSearch
     */
    public function getSearch( $parentSearchBuilder );

    /**
     * @param $baseResult
     * @param $extensionResult
     * @return mixed
     */
    public function transformResult( $baseResult, $extensionResult );

}