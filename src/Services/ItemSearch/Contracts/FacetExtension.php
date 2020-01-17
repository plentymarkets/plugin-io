<?php
namespace IO\Services\ItemSearch\Contracts;

use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;

/**
 * Class FacetExtension
 * @package IO\Services\ItemSearch\Contracts
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Contracts\FacetExtension
 */
interface FacetExtension
{
    /**
     * @return AggregationInterface
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Contracts\FacetExtension::getAggregation()
     */
    public function getAggregation():AggregationInterface;

    /**
     * @param array $result
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Contracts\FacetExtension::mergeIntoFacetsList()
     */
    public function mergeIntoFacetsList($result):array;

    /**
     * @param $filtersList
     * @return mixed
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Contracts\FacetExtension::extractFilterParams()
     */
    public function extractFilterParams($filtersList);
}
