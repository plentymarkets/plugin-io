<?php //strict

namespace IO\Services;

use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Application;

/**
 * Service Class SalesPriceService
 *
 * This service class contains functions related to sales prices.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class SalesPriceService
{
    private $app;
    private $salesPriceSearchRepo;
    /** @var ContactRepositoryContract $contactRepository */
    private $contactRepository;
    /** @var CheckoutRepositoryContract $checkoutRepository */
    private $checkoutRepository;
    private $basketService;

    private $classId = null;
    private $singleAccess = null;
    private $currency = null;
    private $plentyId = null;
    private $shippingCountryId = null;
    private $referrerId = null;

    /**
     * SalesPriceService constructor.
     * @param Application $app
     * @param SalesPriceSearchRepositoryContract $salesPriceSearchRepo
     * @param ContactRepositoryContract $contactRepository
     * @param CheckoutRepositoryContract $checkoutRepository
     * @param BasketService $basketService
     */
    public function __construct(
        Application $app,
        SalesPriceSearchRepositoryContract $salesPriceSearchRepo,
        ContactRepositoryContract $contactRepository,
        CheckoutRepositoryContract $checkoutRepository,
        BasketService $basketService
    )
    {
        $this->app = $app;
        $this->salesPriceSearchRepo = $salesPriceSearchRepo;
        $this->contactRepository = $contactRepository;
        $this->checkoutRepository = $checkoutRepository;
        $this->basketService = $basketService;

        $this->init();
    }

    /**
     * Set the contact class id used for calculations
     * @param int $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
        return $this;
    }

    /**
     * Set the currency used for calculations
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set the id for the shipping country used for calculations
     * @param int $shippingCountryId
     * @return $this
     */
    public function setShippingCountryId($shippingCountryId)
    {
        $this->shippingCountryId = $shippingCountryId;
        return $this;
    }

    private function init()
    {
        $contact = $this->contactRepository->getContact();

        if ($contact instanceof Contact) {
            $this->classId = $contact->classId;
            $this->singleAccess = $contact->singleAccess;
        }

        $this->currency = $this->checkoutRepository->getCurrency();
        $this->shippingCountryId = $this->checkoutRepository->getShippingCountryId();
        $this->plentyId = $this->app->getPlentyId();
        $this->referrerId = $this->basketService->getBasket()->referrerId;
    }

    /**
     * Get a sales price for a specific variation
     * @param int $variationId A variation id to get sales price for
     * @param string $type Optional: What type of sales price to get (Default: 'default')
     * @param int $quantity Optional: Quantity of the variation. Used for graduated prices.
     * @return SalesPriceSearchResponse
     */
    public function getSalesPriceForVariation(int $variationId, $type = 'default', int $quantity = 1)
    {
        /**
         * @var SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = $this->getSearchRequest($variationId, $type, $quantity);

        /**
         * @var SalesPriceSearchResponse $salesPrice
         */
        $salesPrice = $this->salesPriceSearchRepo->search($salesPriceSearchRequest);


        return $this->applyCurrencyConversion($salesPrice);
    }

    /**
     * Get all sales prices of a single type for a specific variation
     * @param int $variationId The variation to find sales prices for
     * @param string $type The type of sales prices to find
     * @return array
     */
    public function getAllSalesPricesForVariation(int $variationId, $type = 'default')
    {
        /**
         * @var SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = $this->getSearchRequest($variationId, $type, -1);

        /**
         * @var array $salesPrices
         */
        $salesPrices = $this->salesPriceSearchRepo->searchAll($salesPriceSearchRequest);

        $convertedSalesPrices = [];
        foreach ($salesPrices as $salesPrice) {
            $convertedSalesPrices[] = $this->applyCurrencyConversion($salesPrice);
        }

        return $convertedSalesPrices;
    }

    /**
     * Apply currency conversions to the prices of a sales price
     * @param SalesPriceSearchResponse $salesPrice A sales price
     * @return SalesPriceSearchResponse
     */
    public function applyCurrencyConversion(SalesPriceSearchResponse $salesPrice): SalesPriceSearchResponse
    {
        $salesPrice->price = $salesPrice->price * $salesPrice->conversionFactor;
        $salesPrice->priceNet = $salesPrice->priceNet * $salesPrice->conversionFactor;
//        $salesPrice->basePrice                  = $salesPrice->basePrice * $salesPrice->conversionFactor;
//        $salesPrice->basePriceNet               = $salesPrice->basePriceNet * $salesPrice->conversionFactor;
//        $salesPrice->unitPrice                  = $salesPrice->unitPrice * $salesPrice->conversionFactor;
//        $salesPrice->unitPriceNet               = $salesPrice->unitPriceNet * $salesPrice->conversionFactor;
        $salesPrice->customerClassDiscount = $salesPrice->customerClassDiscount * $salesPrice->conversionFactor;
        $salesPrice->customerClassDiscountNet = $salesPrice->customerClassDiscountNet * $salesPrice->conversionFactor;
        $salesPrice->categoryDiscount = $salesPrice->categoryDiscount * $salesPrice->conversionFactor;
        $salesPrice->categoryDiscountNet = $salesPrice->categoryDiscountNet * $salesPrice->conversionFactor;

        return $salesPrice;
    }

    private function getSearchRequest(int $variationId, $type, int $quantity)
    {
        /**
         * @var SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);

        $salesPriceSearchRequest->variationId = $variationId;
        $salesPriceSearchRequest->accountId = 0;
        $salesPriceSearchRequest->accountType = $this->singleAccess;
        $salesPriceSearchRequest->countryId = $this->shippingCountryId;
        $salesPriceSearchRequest->currency = $this->currency;
        $salesPriceSearchRequest->customerClassId = $this->classId;
        $salesPriceSearchRequest->plentyId = $this->plentyId;
        $salesPriceSearchRequest->quantity = $quantity;
        $salesPriceSearchRequest->referrerId = $this->referrerId;
        $salesPriceSearchRequest->type = $type;

        return $salesPriceSearchRequest;
    }
}
