<?php

namespace IO\Helper;

use IO\Services\CustomerService;
use Plenty\Modules\Accounting\Vat\Contracts\VatRepositoryContract;
use Plenty\Plugin\Application;

class VatConverter
{
    /** @var VatRepositoryContract $vatRepo */
    private $vatRepo;
    
    public function __construct(VatRepositoryContract $vatRepo)
    {
        $this->vatRepo = $vatRepo;
    }
    
    public function getDefaultVat()
    {
        $defaultVat = $this->vatRepo->getStandardVat(pluginApp(Application::class)->getPlentyId())->vatRates->first();
        return $defaultVat;
    }
    
    public function convertToGross($amount)
    {
        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);
        $contactClassData = $customerService->getContactClassData($customerService->getContactClassId());
        
        if(isset($contactClassData['showNetPrice']) && $contactClassData['showNetPrice'])
        {
            return $amount + (($amount * $this->getDefaultVat()->vatRate) / 100);
        }
        
        return $amount;
    }
}