<?php //strict

namespace IO\Services;

use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Plugin\Application;

class SalesPriceService
{
    private $app;
    private $salesPriceSearchRepo;
    private $customerService;
    private $checkoutService;

    private $classId = null;
    private $singleAccess = null;
    private $currency = null;
    private $plentyId = null;
    private $shippingCountryId = null;

    /**
     * SalesPriceService constructor.
     * @param Application $app
     * @param SalesPriceSearchRepositoryContract $salesPriceSearchRepo
     * @param CustomerService $customerService
     * @param CheckoutService $checkoutService
     */
    public function __construct(
        Application $app,
        SalesPriceSearchRepositoryContract $salesPriceSearchRepo,
        CustomerService $customerService,
        CheckoutService $checkoutService
    )
    {
        $this->app                  = $app;
        $this->salesPriceSearchRepo = $salesPriceSearchRepo;
        $this->customerService      = $customerService;
        $this->checkoutService      = $checkoutService;

        $this->init();
    }

    /**
     * @param null $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;
        return $this;
    }

    /**
     * @param null $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @param null $shippingCountryId
     * @return $this
     */
    public function setShippingCountryId($shippingCountryId)
    {
        $this->shippingCountryId = $shippingCountryId;
        return $this;
    }

    private function init()
    {
        $contact = $this->customerService->getContact();

        if ($contact instanceof Contact) {
            $this->classId      = $contact->classId;
            $this->singleAccess = $contact->singleAccess;
        }

        $this->currency          = $this->checkoutService->getCurrency();
        $this->shippingCountryId = $this->checkoutService->getShippingCountryId();
        $this->plentyId          = $this->app->getPlentyId();
    }

    /**
     * @param int $variationId
     * @param string $type
     * @param int $quantity
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

        return $salesPrice;
    }
    
    /**
     * @param int $variationId
     * @param string $type
     * @param int $quantity
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
        
        return $salesPrices;
    }
    
    private  function getSearchRequest(int $variationId, $type, int $quantity)
    {
        /**
         * @var SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
    
        $salesPriceSearchRequest->variationId     = $variationId;
        $salesPriceSearchRequest->accountId       = 0;
        $salesPriceSearchRequest->accountType     = $this->singleAccess;
        $salesPriceSearchRequest->countryId       = $this->shippingCountryId;
        $salesPriceSearchRequest->currency        = $this->currency;
        $salesPriceSearchRequest->customerClassId = $this->classId;
        $salesPriceSearchRequest->plentyId        = $this->plentyId;
        $salesPriceSearchRequest->quantity        = $quantity;
        $salesPriceSearchRequest->referrerId      = 1; //TODO set to real referrer
        $salesPriceSearchRequest->type            = $type;
        
        return $salesPriceSearchRequest;
    }
}