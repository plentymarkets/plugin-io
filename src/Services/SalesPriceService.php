<?php //strict

namespace IO\Services;

use Plenty\Modules\Item\SalesPrice\Contracts\SalesPriceSearchRepositoryContract;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchRequest;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Plugin\Application;
use IO\Services\CustomerService;
use IO\Services\CheckoutService;

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
    
    public function __construct(Application $app, SalesPriceSearchRepositoryContract $salesPriceSearchRepo, CustomerService $customerService, CheckoutService $checkoutService)
    {
        $this->app = $app;
        $this->salesPriceSearchRepo = $salesPriceSearchRepo;
        $this->customerService = $customerService;
        $this->checkoutService = $checkoutService;

        $this->init();
    }
    
    private function init()
    {
        $contact = $this->customerService->getContact();

        if($contact instanceof Contact)
        {
            $this->classId = $contact->classId;
            $this->singleAccess = $contact->singleAccess;
        }

        $this->currency = $this->checkoutService->getCurrency();
        $this->shippingCountryId = $this->checkoutService->getShippingCountryId();
        $this->plentyId = $this->app->getPlentyId();
    }

    public function getSalesPriceForVariation(int $variationId, $type = 'default', int $quantity = 1)
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
        $salesPriceSearchRequest->referrerId = 1;
        $salesPriceSearchRequest->type = $type;
    
        /**
         * @var SalesPriceSearchResponse $salesPrice
         */
        $salesPrice = $this->salesPriceSearchRepo->search($salesPriceSearchRequest);
        
        return $salesPrice;
    }
}