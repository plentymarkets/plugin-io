<?php

namespace IO\Contracts;

use IO\Services\ItemSearch\Factories\BaseSearchFactory;
use IO\Services\VdiSearch\Factories\BaseSearchFactory as VdiBaseSearchFactory;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Item\Search\Filter\TextFilter;

interface VariationSearchFactoryContract
{
    const SORTING_ORDER_ASC     = ElasticSearch::SORTING_ORDER_ASC;
    const SORTING_ORDER_DESC    = ElasticSearch::SORTING_ORDER_DESC;
    
    const INHERIT_AGGREGATIONS  = 'aggregations';
    const INHERIT_COLLAPSE      = 'collapse';
    const INHERIT_EXTENSIONS    = 'extensions';
    const INHERIT_FILTERS       = 'filters';
    const INHERIT_MUTATORS      = 'mutators';
    const INHERIT_PAGINATION    = 'pagination';
    const INHERIT_RESULT_FIELDS = 'resultFields';
    const INHERIT_SORTING       = 'sorting';
}
