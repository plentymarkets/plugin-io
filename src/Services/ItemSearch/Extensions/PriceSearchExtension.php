<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Helper\VariationPriceList;
use IO\Services\CustomerService;

/**
 * Class PriceSearchExtension
 *
 * Search and append all prices for each item in search result.
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class PriceSearchExtension implements ItemSearchExtension
{
    private $quantities;

    public function __construct( $quantities = [] )
    {
        $this->quantities = $quantities;
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
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $customerClassMinimumOrderQuantity = $customerService->getContactClassMinimumOrderQuantity();

            foreach ( $baseResult['documents'] as $key => $variation )
            {
                if ( (int)$variation['data']['variation']['id'] > 0 )
                {
                    $variationId        = $variation['data']['variation']['id'];
                    $minimumQuantity    = $variation['data']['variation']['minimumOrderQuantity'];
                    if ( (float)$minimumQuantity === 0 )
                    {
                        // mimimum order quantity is not defined => get smallest possible quantity depending on interval order quantity
                        if ( (float)$variation['data']['variation']['intervalOrderQuantity'] > 0 )
                        {
                            $minimumQuantity = $variation['data']['variation']['intervalOrderQuantity'];
                        }
                        else
                        {
                            // no interval quantity defined => minimum order quantity should be 1
                            $variation['data']['variation']['intervalOrderQuantity'] = 1;
                            $minimumQuantity = 1;
                        }
                    }

                    if ( (float)$customerClassMinimumOrderQuantity > $minimumQuantity )
                    {
                        // minimum order quantity is overridden by contact class
                        $minimumQuantity = $customerClassMinimumOrderQuantity;
                    }

                    // assign generated minimum quantity
                    $variation['data']['variation']['minimumOrderQuantity'] = $minimumQuantity;

                    if ( (float)$variation['data']['variation']['maximumOrderQuantity'] <= 0 )
                    {
                        // remove invalid maximum order quantity
                        $variation['data']['variation']['maximumOrderQuantity'] = null;
                    }
                    $maximumOrderQuantity = $variation['data']['variation']['maximumOrderQuantity'];

                    $lot = 0;
                    $unit = null;
                    if ( $variation['data']['variation']['mayShowUnitPrice'] )
                    {
                        $lot = $variation['data']['unit']['content'];
                        $unit = $variation['data']['unit']['unitOfMeasurement'];
                    }


                    $priceList = VariationPriceList::create( $variationId, $minimumQuantity, $maximumOrderQuantity, $lot, $unit );

                    // assign minimum order quantity from price list (may be recalculated depending on available graduated prices)
                    $variation['data']['variation']['minimumOrderQuantity'] = $priceList->minimumOrderQuantity;


                    $quantity = $priceList->minimumOrderQuantity;

                    if ( isset($this->quantities[$variationId])
                        && (float)$this->quantities[$variationId] > 0 )
                    {
                        // override quantity by options
                        $quantity = (float)$this->quantities[$variationId];
                    }

                    $variation['data']['calculatedPrices'] = $priceList->getCalculatedPrices( $quantity );
                    $variation['data']['prices'] = $priceList->toArray( $quantity );


                    $baseResult['documents'][$key] = $variation;
                }
            }
        }

        return $baseResult;
    }
}