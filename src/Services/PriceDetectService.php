<?php

namespace IO\Services;

use Plenty\Legacy\Services\Item\Variation\DetectSalesPriceService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Plugin\Application;
use IO\Services\CustomerService;
use IO\Services\BasketService;


/**
 * Class PriceDetectService
 * @package IO\Services
 */
class PriceDetectService
{
    private $classId = null;
    private $singleAccess = null;
    private $currency = null;
    private $plentyId = null;
    private $shippingCountryId = null;
    
    /**
     * @var DetectSalesPriceService
     */
    private $detectSalesPriceService;
    
    /**
     * @var CustomerService
     */
    private $customerService;
    
    /**
     * @var Application
     */
    private $app;
    
    /**
     * @var CheckoutService
     */
    private $checkoutService;
    
    /**
     * @var BasketService $basketService
     */
    private $basketService;
    
    private $referrerId;
    
    /**
     * PriceDetectService constructor.
     * @param DetectSalesPriceService $detectSalesPriceService
     * @param \IO\Services\CustomerService $customerService
     * @param Application $app
     * @param CheckoutService $checkoutService
     */
    public function __construct(DetectSalesPriceService $detectSalesPriceService,
                                CustomerService $customerService,
                                Application $app,
                                CheckoutService $checkoutService,
                                BasketService $basketService)
    {
        $this->detectSalesPriceService = $detectSalesPriceService;
        $this->customerService = $customerService;
        $this->app = $app;
        $this->checkoutService = $checkoutService;
        $this->basketService = $basketService;
        
        $this->init();
    }
    
    private function init()
    {
        $contact = $this->customerService->getContact();
        
        if ($contact instanceof Contact) {
            $this->singleAccess = $contact->singleAccess;
        }

        $this->classId           = $this->customerService->getContactClassId();
        $this->currency          = $this->checkoutService->getCurrency();
        $this->shippingCountryId = $this->checkoutService->getShippingCountryId();
        $this->plentyId          = $this->app->getPlentyId();
        $this->referrerId        = $this->basketService->getBasket()->referrerId;
    }
    
    public function getPriceIdsForCustomer()
    {
        $this->detectSalesPriceService
            ->setAccountId(0)
            ->setAccountType($this->singleAccess)
            ->setCountryOfDelivery($this->shippingCountryId)
            ->setCurrency($this->currency)
            ->setCustomerClass($this->classId)
            ->setOrderReferrer($this->referrerId)
            ->setPlentyId($this->plentyId)
            ->setQuantity(1)
            ->setType(DetectSalesPriceService::PRICE_TYPE_DEFAULT);
        
        return $this->detectSalesPriceService->detect();
    }
}