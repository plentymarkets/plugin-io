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
    private $removeProperties = false;
    public function __construct($removeProperties = false)
    {
        $this->removeProperties = $removeProperties;
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
        if( is_array($baseResult['documents']) && count($baseResult['documents']) )
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
        if(!is_array($data) || !array_key_exists('properties', $data)) {
            return $data;
        }

        $properties = array_filter($data['properties'], function($property) {
            return $property['property']['isShownOnItemPage']
                || $property['property']['isShownOnItemList']
                || $property['property']['isShownAtCheckout'];
        });

        $orderProperties = array_filter($properties, function($property) {
            return $property['property']['isOderProperty'];
        });

        if($this->removeProperties) {
            $properties = [];
        }

        $data['properties'] = array_values($properties);
        $data['hasOrderProperties'] = count($orderProperties) > 0;

        return $data;
    }
}
