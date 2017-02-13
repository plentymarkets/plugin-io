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
    
    public function __construct(Application $app, SalesPriceSearchRepositoryContract $salesPriceSearchRepo, CustomerService $customerService, CheckoutService $checkoutService)
    {
        $this->app = $app;
        $this->salesPriceSearchRepo = $salesPriceSearchRepo;
        $this->customerService = $customerService;
        $this->checkoutService = $checkoutService;
    }
    
    public function getSalesPriceForVariation(int $variationId, int $quantity = 1)
    {
        /**
         * @var Contact $contact
         */
        $contact = $this->customerService->getContact();
        
        /**
         * @var SalesPriceSearchRequest $salesPriceSearchRequest
         */
        $salesPriceSearchRequest = pluginApp(SalesPriceSearchRequest::class);
        
        $salesPriceSearchRequest->variationId = $variationId;
        $salesPriceSearchRequest->accountId = 0;
        $salesPriceSearchRequest->accountType = $contact->singleAccess;
        $salesPriceSearchRequest->countryId = $this->checkoutService->getShippingCountryId();
        $salesPriceSearchRequest->currency = $this->checkoutService->getCurrency();
        $salesPriceSearchRequest->customerClassId = $contact->classId;
        $salesPriceSearchRequest->plentyId = $this->app->getPlentyId();
        $salesPriceSearchRequest->quantity = $quantity;
        $salesPriceSearchRequest->referrerId = 1;
        $salesPriceSearchRequest->type = '';
    
        /**
         * @var SalesPriceSearchResponse $salesPrice
         */
        $salesPrice = $this->salesPriceSearchRepo->search($salesPriceSearchRequest);
        
        return $salesPrice;
    }
}