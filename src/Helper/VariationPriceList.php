<?php

namespace IO\Helper;

use IO\Extensions\Filters\NumberFormatFilter;
use IO\Services\BasketService;
use IO\Services\CheckoutService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use Plenty\Legacy\Services\Item\Variation\SalesPriceService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Plugin\Application;

class VariationPriceList
{
    /** @var int $variationId */
    private $variationId = 0;

    /** @var float $minimumOrderQuantity */
    public $minimumOrderQuantity = 0.0;

    private $prices = [];

    /** @var NumberFormatFilter $numberFormatFilter */
    private $numberFormatFilter;

    public function __construct( NumberFormatFilter $numberFormatFilter )
    {
        $this->numberFormatFilter = $numberFormatFilter;
    }

    public static function create( int $variationId, $minimumOrderQuantity = 0, $maximumOrderQuantity = null )
    {
        if ( $minimumOrderQuantity === null )
        {
            $minimumOrderQuantity = 0;
        }

        /** @var VariationPriceList $instance */
        $instance = pluginApp( VariationPriceList::class);
        $instance->init( $variationId, $minimumOrderQuantity, $maximumOrderQuantity, 'default' );
        $instance->init( $variationId, $minimumOrderQuantity, $maximumOrderQuantity, 'rrp' );
        $instance->init( $variationId, $minimumOrderQuantity, $maximumOrderQuantity, 'specialOffer' );

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

    public function findPriceForQuantity( float $quantity, $type = 'default' )
    {
        $result = null;
        if ( array_key_exists( $type, $this->prices ) )
        {
            foreach($this->prices[$type] as $price )
            {
                if ( $price instanceof SalesPriceSearchResponse && (float) $price->minimumOrderQuantity <= $quantity )
                {
                    $result = $price;
                }
            }
        }
        return $result;
    }

    public function getGraduatedPrices()
    {
        $graduatedPrices = [];

        foreach($this->prices['default'] as $price )
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

        return $graduatedPrices;
    }

    public function getBasePrice( $lot, $unit, $salesPrice, $lang = null )
    {
        /** @var SalesPriceService $basePriceService */
        $basePriceService = pluginApp( SalesPriceService::class );
        $basePriceString = '';

        if ( $lot > 0 && strlen($unit) > 0 )
        {
            $basePrice = [];
            list( $basePrice['lot'], $basePrice['price'], $basePrice['unitKey'] ) = $basePriceService->getUnitPrice($lot, $salesPrice->unitPrice, $unit);

            /**
             * @var UnitRepositoryContract $unitRepository
             */
            $unitRepository = pluginApp(UnitRepositoryContract::class);

            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);

            $unitData = $authHelper->processUnguarded( function() use ($unitRepository, $basePrice)
            {
                $unitRepository->setFilters(['unitOfMeasurement' => $basePrice['unitKey']]);
                return $unitRepository->all(['*'], 1, 1);
            });


            $unitId = $unitData->getResult()->first()->id;

            /** @var UnitNameRepositoryContract $unitNameRepository */
            $unitNameRepository = pluginApp(UnitNameRepositoryContract::class);
            if ( $lang === null )
            {
                $lang = pluginApp(SessionStorageService::class)->getLang();
            }
            $unitName = $unitNameRepository->findOne($unitId, $lang)->name;

            $basePriceString = $this->numberFormatFilter->formatMonetary($basePrice['price'], $salesPrice->currency).' / '.($basePrice['lot'] > 1 ? $basePrice['lot'].' ' : '').$unitName;
        }

        return $basePriceString;
    }

    public function toArray( $lot = 0, $unit = null )
    {
        $defaultPrice = $this->findPriceForQuantity( $this->minimumOrderQuantity );
        $rrp = $this->findPriceForQuantity( $this->minimumOrderQuantity, 'rrp');
        $specialOffer = $this->findPriceForQuantity( $this->minimumOrderQuantity, 'specialOffer');

        return [
            'default' => $defaultPrice,
            'formatted' => [
                'basePrice' => $this->getBasePrice( $lot, $unit, $defaultPrice ),
                'defaultPrice' => $this->numberFormatFilter->formatMonetary( $defaultPrice->price, $defaultPrice->currency ),
                'defaultUnitPrice' => $this->numberFormatFilter->formatMonetary( $defaultPrice->unitPrice, $defaultPrice->currency ),
                'rrpPrice' => $this->numberFormatFilter->formatMonetary( $rrp->price, $rrp->currency ),
                'rrpUnitPrice' => $this->numberFormatFilter->formatMonetary( $rrp->unitPrice, $rrp->currency )
            ],
            'graduatedPrices' => $this->getGraduatedPrices(),
            'rrp' => $rrp,
            'specialOffer' => $specialOffer
        ];
    }

    private function init( int $variationId, float $minimumOrderQuantity, $maximumOrderQuantity, $type )
    {
        $this->variationId = $variationId;
        $this->minimumOrderQuantity = $minimumOrderQuantity;

        $salesPriceSearchRequest = $this->getSearchRequest( $variationId, $type, -1 );

        /** @var SalesPriceSearchRepositoryContract $salesPriceSearchRepo */
        $salesPriceSearchRepo = pluginApp( SalesPriceSearchRepositoryContract::class );

        $salesPrices = $salesPriceSearchRepo->searchAll( $salesPriceSearchRequest );

        $quantities = [];
        $this->prices[$type] = [];
        foreach( $salesPrices as $price )
        {
            if ( $price instanceof SalesPriceSearchResponse
                && !in_array( $price->minimumOrderQuantity, $quantities )
                && ($maximumOrderQuantity === null || $price->minimumOrderQuantity <= $maximumOrderQuantity))
            {
                $this->prices[$type][] = $price;
                $quantities[] = $price->minimumOrderQuantity;
            }
        }
    }

    private function getSearchRequest( int $variationId, string $type = 'default', float $quantity = 0 )
    {
        /** @var SalesPriceSearchRequest $salesPriceSearchRequest */
        $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
        $salesPriceSearchRequest->variationId = $variationId;
        $salesPriceSearchRequest->accountId   = 0;
        $salesPriceSearchRequest->quantity    = $quantity;
        $salesPriceSearchRequest->type        = $type;

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
}