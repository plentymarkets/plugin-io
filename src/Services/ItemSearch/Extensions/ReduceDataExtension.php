<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Helper\VariationPriceList;
use IO\Services\CustomerService;
use Plenty\Legacy\Repositories\Frontend\CurrencyExchangeRepository;

/**
 * Class ReduceDataExtension
 *
 * Reduce result objects by filtering unnecessary entries
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class ReduceDataExtension implements ItemSearchExtension
{
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function getSearch( $parentSearchBuilder )
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        if( count($baseResult['documents'] ) )
        {
            $baseResult['documents'] = array_map(function($document)
            {
                $document['data'] = $this->reduceData($document['data']);
                return $document;
            }, $baseResult['documents']);
        }

        return $baseResult;
    }

    private function reduceData($data)
    {
        $properties = array_filter($data['properties'], function($property)
        {
            return $property['property']['isShownOnItemPage']
                || $property['property']['isShownOnItemList']
                || $property['property']['isShownAtCheckout'];
        });

        $data['properties'] = array_values($properties);

        return $data;
    }
}