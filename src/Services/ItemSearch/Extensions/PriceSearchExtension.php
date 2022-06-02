<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Helper\Utils;
use IO\Helper\VariationPriceList;
use IO\Services\CustomerService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class PriceSearchExtension
 *
 * Search and append all prices for each item in search result.
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class PriceSearchExtension implements ItemSearchExtension
{
    use Loggable;

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
        if(is_array($baseResult['documents']) && count($baseResult['documents']))
        {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $customerClassMinimumOrderQuantity = $customerService->getContactClassMinimumOrderQuantity();

            foreach ( $baseResult['documents'] as $key => $variation )
            {
                if ( (int)$variation['data']['variation']['id'] > 0 )
                {
                    $itemId             = $variation['data']['item']['id'];
                    $variationId        = $variation['data']['variation']['id'];
                    $minimumQuantity    = $variation['data']['variation']['minimumOrderQuantity'];
                    if ( is_null($minimumQuantity) || (float)$minimumQuantity === 0 )
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


                    $priceList = VariationPriceList::create( $variationId, $itemId, $minimumQuantity, $maximumOrderQuantity, $lot, $unit );

                    // assign minimum order quantity from price list (may be recalculated depending on available graduated prices)
                    $variation['data']['variation']['minimumOrderQuantity'] = $priceList->minimumOrderQuantity;


                    $quantity = $priceList->minimumOrderQuantity;

                    if ( isset($this->quantities[$variationId])
                        && (float)$this->quantities[$variationId] > 0 )
                    {
                        // override quantity by options
                        $quantity = (float)$this->quantities[$variationId];
                    }

                    $variation['data']['prices'] = $priceList->toArray( $quantity );

                    if ( $variation['data']['prices']['default']['unitPrice']['value'] <= 0 || $variation['data']['prices']['default']['price']['value'] <= 0)
                    {
                        $this->getLogger(__CLASS__)->warning('IO::Debug.PriceSearchExtension_freeItemFound', [
                            'variation' => $variation,
                            'isAdminPreview' => Utils::isAdminPreview()
                        ]);
                    }

                    if ( array_key_exists('properties', $variation['data']) )
                    {
                        $variation['data']['properties'] = $this->convertPropertySurcharges(
                            $variation['data']['properties'],
                            $priceList
                        );
                    }

                    $baseResult['documents'][$key] = $variation;
                }
            }
        }

        return $baseResult;
    }

    /**
     * @param array                 $properties
     * @param VariationPriceList    $priceList
     * @return array
     */
    private function convertPropertySurcharges( $properties, $priceList )
    {
        $result = [];

        foreach( $properties as $property )
        {
            if ( $property['group']['isSurchargePercental'] )
            {
                $defaultPrice = $priceList->getDefaultPrice();
                
                $property['property']['surcharge'] = $defaultPrice->unitPrice * ($property['property']['surcharge'] / 100);
            }
    
            $property['property']['surcharge'] = $priceList->convertGrossNet( $property['property']['surcharge'] );
            $property['property']['surcharge'] = $priceList->convertCurrency( $property['property']['surcharge'] );

            $property['surcharge'] = $priceList->convertGrossNet( $property['surcharge'] );
            $property['surcharge'] = $priceList->convertCurrency( $property['surcharge'] );

            $result[] = $property;
        }

        return $result;
    }
}
