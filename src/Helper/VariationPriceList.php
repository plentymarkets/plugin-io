<?php

namespace IO\Helper;

use IO\Extensions\Filters\NumberFormatFilter;
use IO\Services\BasketService;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Services\UnitService;
use Plenty\Legacy\Services\Item\Variation\SalesPriceService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\CachingRepository;

class VariationPriceList
{
    use MemoryCache;

    const TYPE_DEFAULT          = 'default';
    const TYPE_RRP              = 'rrp';
    const TYPE_SPECIAL_OFFER    = 'specialOffer';

    /** @var int $variationId */
    public $variationId = 0;

    /** @var float $minimumOrderQuantity */
    public $minimumOrderQuantity = 0.0;

    /** @var float $maximumOrderQuantity */
    public $maximumOrderQuantity = null;

    public $lot;

    public $unit;

    private $prices = [];

    /** @var NumberFormatFilter $numberFormatFilter */
    private $numberFormatFilter;

    /** @var UnitService $unitService */
    private $unitService;

    public function __construct( NumberFormatFilter $numberFormatFilter, UnitService $unitService )
    {
        $this->numberFormatFilter = $numberFormatFilter;
        $this->unitService = $unitService;
    }

    public static function create( int $variationId, $minimumOrderQuantity = 0, $maximumOrderQuantity = null, $lot = 0, $unit = null )
    {
        if ( $minimumOrderQuantity === null )
        {
            $minimumOrderQuantity = 0;
        }

        /** @var VariationPriceList $instance */
        $instance = pluginApp( VariationPriceList::class);

        $instance->init( $variationId, $minimumOrderQuantity, $maximumOrderQuantity, $lot, $unit );

        // check if default price for minimum order quantity exists
        if ( $instance->findPriceForQuantity( $minimumOrderQuantity ) === null )
        {
            // set minimum order quantity to first graduated price
            $minimumGraduatedQuantity = -1;
            foreach( $instance->getGraduatedPrices() as $price )
            {
                if ( $minimumGraduatedQuantity === -1 || $price['minimumOrderQuantity'] < $minimumGraduatedQuantity )
                {
                    $minimumGraduatedQuantity = $price['minimumOrderQuantity'];
                }
            }
            $instance->minimumOrderQuantity = $minimumGraduatedQuantity;
        }
        return $instance;
    }

    public function findPriceForQuantity( float $quantity, $type = self::TYPE_DEFAULT )
    {
        $result = null;
        $minimumOrderQuantity = -1.0;
        if ( array_key_exists( $type, $this->prices ) )
        {
            foreach($this->prices[$type] as $price )
            {
                if ( $price instanceof SalesPriceSearchResponse && (float)$price->minimumOrderQuantity <= $quantity && (float)$price->minimumOrderQuantity > $minimumOrderQuantity)
                {
                    $result = $price;
                    $minimumOrderQuantity = (float)$price->minimumOrderQuantity;
                }
            }
        }
        return $result;
    }

    public function getGraduatedPrices( $showNetPrice = false )
    {
        $graduatedPrices = [];

        foreach($this->prices[self::TYPE_DEFAULT] as $price )
        {
            if($price instanceof SalesPriceSearchResponse)
            {
                $graduatedPrices[] = $this->preparePrice( $price, $showNetPrice );
            }
        }

        return $graduatedPrices;
    }

    public function getBasePrice( $unitPrice, $currency, $lang = null )
    {
        /** @var SalesPriceService $basePriceService */
        $basePriceService = pluginApp( SalesPriceService::class );
        $basePriceString = '';

        if ( $this->lot > 0 && strlen($this->unit) > 0 )
        {
            $basePrice = [];
            list( $basePrice['lot'], $basePrice['price'], $basePrice['unitKey'] ) = $basePriceService->getUnitPrice($this->lot, $unitPrice, $this->unit);

            $unitName = $this->unitService->getUnitNameByKey( $basePrice['unitKey'], $lang );

            $basePriceString = $this->numberFormatFilter->formatMonetary($basePrice['price'], $currency).' / '.($basePrice['lot'] > 1 ? $basePrice['lot'].' ' : '').$unitName;
        }

        return $basePriceString;
    }

    public function toArray( $quantity = null )
    {
        if ( $quantity === null )
        {
            $quantity = $this->minimumOrderQuantity;
        }

        /** @var CustomerService $customerService */
        $customerService = pluginApp( CustomerService::class );
        $showNetPrice = $customerService->showNetPrices();

        $defaultPrice   = $this->findPriceForQuantity( $quantity );
        $rrp            = $this->findPriceForQuantity( $quantity, self::TYPE_RRP );
        $specialOffer   = $this->findPriceForQuantity( $quantity, self::TYPE_SPECIAL_OFFER );

        return [
            'default'           => $this->preparePrice( $defaultPrice, $showNetPrice ),
            'rrp'               => $this->preparePrice( $rrp, $showNetPrice ),
            'specialOffer'      => $this->preparePrice( $specialOffer, $showNetPrice ),
            'graduatedPrices'   => $this->getGraduatedPrices( $showNetPrice )
        ];
    }

    public function getCalculatedPrices( $quantity = null )
    {
        if ( $quantity === null )
        {
            $quantity = $this->minimumOrderQuantity;
        }

        $defaultPrice   = $this->findPriceForQuantity( $quantity );
        $rrp            = $this->findPriceForQuantity( $quantity, self::TYPE_RRP );
        $specialOffer   = $this->findPriceForQuantity( $quantity, self::TYPE_SPECIAL_OFFER );
        $graduatedPrices= [];

        foreach($this->prices[self::TYPE_DEFAULT] as $price )
        {
            if($price instanceof SalesPriceSearchResponse)
            {
                $graduatedPrices[] = [
                    'minimumOrderQuantity'  => (float) $price->minimumOrderQuantity,
                    'price'                 => (float) $price->unitPrice,
                    'formatted'             => $this->numberFormatFilter->formatMonetary( $price->unitPrice, $price->currency )
                ];

            }
        }

        return [
            'default' => $defaultPrice,
            'formatted' => [
                'basePrice' => $this->getBasePrice( $defaultPrice->unitPrice, $defaultPrice->currency ),
                'defaultPrice' => $this->numberFormatFilter->formatMonetary( $defaultPrice->price, $defaultPrice->currency ),
                'defaultUnitPrice' => $this->numberFormatFilter->formatMonetary( $defaultPrice->unitPrice, $defaultPrice->currency ),
                'rrpPrice' => $this->numberFormatFilter->formatMonetary( $rrp->price, $rrp->currency ),
                'rrpUnitPrice' => $this->numberFormatFilter->formatMonetary( $rrp->unitPrice, $rrp->currency )
            ],
            'graduatedPrices' => $graduatedPrices,
            'rrp' => $rrp,
            'specialOffer' => $specialOffer
        ];
    }

    private function init( $variationId, $minimumOrderQuantity, $maximumOrderQuantity, $lot, $unit )
    {
        $this->variationId          = $variationId;
        $this->minimumOrderQuantity = $minimumOrderQuantity;
        $this->maximumOrderQuantity = $maximumOrderQuantity;
        $this->lot                  = $lot;
        $this->unit                 = $unit;

        /** @var SalesPriceSearchRepositoryContract $priceSearchRepo */
        $priceSearchRepo = pluginApp( SalesPriceSearchRepositoryContract::class );

        // prepare search request
        $priceSearchRequest = $this->getSearchRequest( $this->variationId, self::TYPE_DEFAULT, -1 );

        // search default prices
        $this->fetchPrices(
            $priceSearchRepo->searchAll( $priceSearchRequest ),
            self::TYPE_DEFAULT
        );

        // search recommended retail prices
        $priceSearchRequest->type = self::TYPE_RRP;
        $this->fetchPrices(
            $priceSearchRepo->searchAll( $priceSearchRequest ),
            self::TYPE_RRP
        );

        // search special offer prices
        $priceSearchRequest->type = self::TYPE_SPECIAL_OFFER;
        $this->fetchPrices(
            $priceSearchRepo->searchAll( $priceSearchRequest ),
            self::TYPE_SPECIAL_OFFER
        );


    }

    private function fetchPrices( $prices, $type )
    {
        $quantities = [];
        $this->prices[$type] = [];
        foreach( $prices as $price )
        {
            if ( $price instanceof SalesPriceSearchResponse
                && !in_array( $price->minimumOrderQuantity, $quantities )
                && ($this->maximumOrderQuantity === null || $price->minimumOrderQuantity <= $this->maximumOrderQuantity))
            {
                $this->prices[$type][] = $price;
                $quantities[] = $price->minimumOrderQuantity;
            }
        }
    }

    private function getSearchRequest( int $variationId, string $type = self::TYPE_DEFAULT, float $quantity = 0 )
    {
        /** @var SalesPriceSearchRequest $salesPriceSearchRequest */
        $salesPriceSearchRequest = $this->fromMemoryCache(
            "salesPriceRequest",
            function()
            {
                /** @var SalesPriceSearchRequest $salesPriceSearchRequest */
                $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
                $salesPriceSearchRequest->accountId   = 0;

                /** @var CustomerService $customerService */
                $customerService = pluginApp( CustomerService::class );
                $contact = $customerService->getContact();

                if ( $contact instanceof Contact )
                {
                    $salesPriceSearchRequest->accountType = $contact->singleAccess;
                }
                $salesPriceSearchRequest->customerClassId = $customerService->getContactClassId();

                /** @var CheckoutService $checkoutService */
                $checkoutService = pluginApp( CheckoutService::class );

                $salesPriceSearchRequest->countryId = $checkoutService->getShippingCountryId();
                $salesPriceSearchRequest->currency  = $checkoutService->getCurrency();

                /** @var BasketService $basketService */
                $basketService = pluginApp( BasketService::class );
                $salesPriceSearchRequest->referrerId = $basketService->getBasket()->referrerId;

                /** @var Application $app */
                $app = pluginApp( Application::class );
                $salesPriceSearchRequest->plentyId = $app->getPlentyId();

                return $salesPriceSearchRequest;
            }
        );

        $salesPriceSearchRequest->variationId = $variationId;
        $salesPriceSearchRequest->quantity    = $quantity;
        $salesPriceSearchRequest->type        = $type;
        return $salesPriceSearchRequest;
    }

    private function preparePrice( $price, $showNetPrice = false )
    {
        if ( $price === null )
        {
            return null;
        }

        return [
            'price'                 => [
                'value'     => $showNetPrice ? $price->priceNet : $price->price,
                'formatted' => $this->numberFormatFilter->formatMonetary( $showNetPrice ? $price->priceNet : $price->price, $price->currency )
            ],
            'unitPrice'             => [
                'value'     => $showNetPrice ? $price->unitPriceNet : $price->unitPrice,
                'formatted' => $this->numberFormatFilter->formatMonetary( $showNetPrice ? $price->unitPriceNet : $price->unitPrice, $price->currency )
            ],
            'basePrice'             => $this->getBasePrice( $showNetPrice ? $price->unitPriceNet : $price->unitPrice, $price->currency ),
            'minimumOrderQuantity'  => (float) $price->minimumOrderQuantity,
            'contactClassDiscount'  => [
                'percent'   => $price->customerClassDiscountPercent,
                'amount'    => $showNetPrice ? $price->customerClassDiscountNet : $price->customerClassDiscount
            ],
            'categoryDiscount'      => [
                'percent'   => $price->categoryDiscountPercent,
                'amount'    => $showNetPrice ? $price->categoryDiscountNet : $price->categoryDiscount
            ],
            'currency'              => $price->currency,
            'vat'                   => [
                'id'        => $price->vatId,
                'value'     => $price->vatValue
            ],
            'isNet'                 => $showNetPrice,
            'data'                  => $price
        ];
    }
}