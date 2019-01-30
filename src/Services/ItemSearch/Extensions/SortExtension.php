<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Helper\VariationPriceList;
use IO\Services\CustomerService;
use Plenty\Legacy\Repositories\Frontend\CurrencyExchangeRepository;

/**
 * Class SortExtension
 *
 * Sort search results in given order
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class SortExtension implements ItemSearchExtension
{
    private $idList;

    public function __construct( $idList = [] )
    {
        $this->idList = $idList;
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
        usort(
            $baseResult["documents"],
            function($documentA, $documentB)
            {
                return array_search( $documentA["id"], $this->idList ) - array_search( $documentB["id"], $this->idList );
            }
        );

        return $baseResult;
    }
}