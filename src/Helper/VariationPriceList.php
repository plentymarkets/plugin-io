<?php

namespace IO\Helper;

use IO\Extensions\Filters\NumberFormatFilter;
use IO\Services\BasketService;
use IO\Services\UnitService;
use Plenty\Legacy\Services\Item\Variation\SalesPriceService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Application;

/**
 * Class VariationPriceList
 *
 * Helper class for getting prices for variations.
 *
 * @package IO\Helper
 * @deprecated since 5.0.0 will be removed in 6.0.0.
 * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList
 */
class VariationPriceList
{
    use MemoryCache;

    /** @var string Represents the default price. */
    const TYPE_DEFAULT          = 'default';
    /** @var string Represents the recommended retail price. */
    const TYPE_RRP              = 'rrp';
    /** @var string Represents the special offer price. */
    const TYPE_SPECIAL_OFFER    = 'specialOffer';

    /** @var array Contains the base prices (without units etc.). */
    public static $basePrices = [];

    /** @var int $itemId Item ID to fetch prices for. */
    public $itemId = 0;

    /** @var int $variationId Variation ID to fetch prices for. */
    public $variationId = 0;

    /** @var float $minimumOrderQuantity The minimum order quantity. */
    public $minimumOrderQuantity = 0.0;

    /** @var float $maximumOrderQuantity The maximum order quantity. */
    public $maximumOrderQuantity = null;

    public $lot;

    public $unit;

    private $prices = [];

    /** @var NumberFormatFilter $numberFormatFilter */
    private $numberFormatFilter;

    /** @var UnitService $unitService */
    private $unitService;

    /** @var LiveShoppingRepositoryContract $liveShoppingRepo */
    private $liveShoppingRepo;

    private $showNetPrice = false;

    /** @var SalesPriceSearchResponse */
    private $defaultPrice;

    public function __construct(
        NumberFormatFilter $numberFormatFilter,
        UnitService $unitService,
        LiveShoppingRepositoryContract $liveShoppingRepo,
        ContactRepositoryContract $contactRepository )
    {
        $this->numberFormatFilter   = $numberFormatFilter;
        $this->unitService       = $unitService;
        $this->showNetPrice         = $contactRepository->showNetPrices();
        $this->liveShoppingRepo     = $liveShoppingRepo;
    }

    /**
     * @param int $variationId
     * @param int $itemId
     * @param int $minimumOrderQuantity
     * @param null $maximumOrderQuantity
     * @param int $lot
     * @param null $unit
     * @return VariationPriceList
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::create()
     */
    public static function create( int $variationId, int $itemId, $minimumOrderQuantity = 0, $maximumOrderQuantity = null, $lot = 0, $unit = null )
    {
        if ( $minimumOrderQuantity === null )
        {
            $minimumOrderQuantity = 0;
        }

        /** @var VariationPriceList $instance */
        $instance = pluginApp( VariationPriceList::class);

        $instance->init( $variationId, $itemId, $minimumOrderQuantity, $maximumOrderQuantity, $lot, $unit );

        // check if default price for minimum order quantity exists.
        if ( $instance->findPriceForQuantity( $minimumOrderQuantity ) === null )
        {
            // set minimum order quantity to first graduated price.
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

    /**
     * Get a price for a specific quantity.
     * @param float $quantity
     * @param string $type
     * @return mixed|SalesPriceSearchResponse|null
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::findPriceForQuantity()
     */
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

    /**
     * Get the graduated prices.
     * @param bool $showNetPrice
     * @return array
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::getGraduatedPrices()
     */
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

    /**
     * @param float $unitPrice
     * @param string $currency
     * @param null $lang
     * @return string
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::getBasePrice()
     */
    public function getBasePrice( $unitPrice, $currency, $lang = null )
    {
        /** @var SalesPriceService $basePriceService */
        $basePriceService = pluginApp( SalesPriceService::class );
        $basePriceString = '';

        if ( $this->lot > 0 && strlen($this->unit) > 0 )
        {
            if(isset(self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit]))
            {
                $basePrice = self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit];
            }
            else
            {
                $basePrice = [];
                list( $basePrice['lot'], $basePrice['price'], $basePrice['unitKey'] ) = $basePriceService->getUnitPrice($this->lot, $unitPrice, $this->unit);
                self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit] = $basePrice;
            }

            $unitName = $this->unitService->getUnitNameByKey($basePrice['unitKey'], $lang );

            $basePriceString = $this->numberFormatFilter->formatMonetary($basePrice['price'], $currency).' / '.($basePrice['lot'] > 1 ? $basePrice['lot'].' ' : '').$unitName;
        }

        return $basePriceString;
    }

    /**
     * @param null $quantity
     * @return array
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::toArray()
     */
    public function toArray( $quantity = null )
    {
        if ( $quantity === null )
        {
            $quantity = $this->minimumOrderQuantity;
        }

        $defaultPrice   = $this->findPriceForQuantity( $quantity );
        $rrp            = $this->findPriceForQuantity( $quantity, self::TYPE_RRP );

        if($this->liveShoppingRepo->itemHasActiveLiveShopping($this->itemId))
        {
            $specialOffer   = $this->findPriceForQuantity( $quantity, self::TYPE_SPECIAL_OFFER );
        }

        return [
            'default'           => $this->preparePrice( $defaultPrice, $this->showNetPrice ),
            'rrp'               => $this->preparePrice( $rrp, $this->showNetPrice ),
            'specialOffer'      => $this->preparePrice( $specialOffer, $this->showNetPrice ),
            'graduatedPrices'   => $this->getGraduatedPrices( $this->showNetPrice )
        ];
    }

    /**
     * @param null $quantity
     * @return array
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::getCalculatedPrices()
     */
    public function getCalculatedPrices( $quantity = null)
    {
        if ( $quantity === null )
        {
            $quantity = $this->minimumOrderQuantity;
        }

        $defaultPrice   = $this->findPriceForQuantity( $quantity );
        $rrp            = $this->findPriceForQuantity( $quantity, self::TYPE_RRP );
        $graduatedPrices= [];

        $specialOffer = null;
        if($this->liveShoppingRepo->itemHasActiveLiveShopping($this->itemId))
        {
            $specialOffer   = $this->findPriceForQuantity( $quantity, self::TYPE_SPECIAL_OFFER );
        }

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

    /**
     * Convert to active currency.
     * @param float $value A price to convert.
     * @return float|int
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::convertCurrency()
     */
    public function convertCurrency( $value )
    {
        $defaultPrice = $this->getDefaultPrice();
        return $value * $defaultPrice->conversionFactor;
    }

    /**
     * Converts a price from net to gross or from gross to net.
     * @param float $value A price to convert.
     * @param false $isNet Is this price a net price?
     * @return float|int
     */
    public function convertGrossNet( $value, $isNet = false )
    {
        $defaultPrice = $this->getDefaultPrice();
        if ( $this->showNetPrice && !$isNet )
        {
            // convert from gross to net.
            return $value / (1 + ($defaultPrice->vatValue / 100));
        }
        else if ( !$this->showNetPrice && $isNet )
        {
            // convert from net to gross.
            return $value * (1 + ($defaultPrice->vatValue / 100));
        }

        // no conversion.
        return $value;
    }

    /**
     * Getter for the default price.
     * @return mixed|SalesPriceSearchResponse|null
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\VariationPriceList::getDefaultPrice()
     */
    public function getDefaultPrice()
    {
        if ( is_null($this->defaultPrice) )
        {
            $this->defaultPrice = $this->findPriceForQuantity($this->minimumOrderQuantity);
        }

        return $this->defaultPrice;
    }

    private function init( $variationId, $itemId, $minimumOrderQuantity, $maximumOrderQuantity, $lot, $unit )
    {
        $this->variationId          = $variationId;
        $this->itemId               = $itemId;
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

                /** @var ContactRepositoryContract $contactRepository */
                $contactRepository = pluginApp(ContactRepositoryContract::class);

                $contact = $contactRepository->getContact();

                if ( $contact instanceof Contact )
                {
                    $salesPriceSearchRequest->accountType = $contact->singleAccess;
                }
                $salesPriceSearchRequest->customerClassId = $contactRepository->getContactClassId();

                /** @var BasketService $basketService */
                $basketService = pluginApp( BasketService::class );
                $salesPriceSearchRequest->referrerId = $basketService->getBasket()->referrerId;

                /** @var Application $app */
                $app = pluginApp( Application::class );
                $salesPriceSearchRequest->plentyId = $app->getPlentyId();

                return $salesPriceSearchRequest;
            }
        );

        /** @var  CheckoutRepositoryContract $checkoutRepository */
        $checkoutRepository = pluginApp(CheckoutRepositoryContract::class);

        $salesPriceSearchRequest->countryId = $checkoutRepository->getShippingCountryId();
        $salesPriceSearchRequest->currency  = $checkoutRepository->getCurrency();
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

        $unitPrice = $showNetPrice ? $price->unitPriceNet : $price->unitPrice;
        return [
            'price'                 => [
                'value'     => $showNetPrice ? $price->priceNet : $price->price,
                'formatted' => $this->numberFormatFilter->formatMonetary( $showNetPrice ? $price->priceNet : $price->price, $price->currency )
            ],
            'unitPrice'             => [
                'value'     => $unitPrice,
                'formatted' => $this->numberFormatFilter->formatMonetary( $unitPrice, $price->currency )
            ],
            'basePrice'             => $this->getBasePrice( $unitPrice, $price->currency ),
            'baseLot'               => self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit]['lot'],
            'baseUnit'              => self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit]['unitKey'],
            'baseSinglePrice'       => self::$basePrices[(string)$this->lot][(string)$unitPrice][$this->unit]['price'],

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
