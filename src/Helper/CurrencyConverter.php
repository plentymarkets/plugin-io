<?php

namespace IO\Helper;

use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;

/**
 * Class CurrencyConverter
 * @package IO\Helper
 */
class CurrencyConverter
{
    /** @var CurrencyExchangeRepositoryContract $currencyExchcangeRepo */
    private $currencyExchcangeRepo;
    
    /**
     * CurrencyConverter constructor.
     * @param CurrencyExchangeRepositoryContract $currencyExchangeRepo
     */
    public function __construct(CurrencyExchangeRepositoryContract $currencyExchangeRepo)
    {
        $this->currencyExchcangeRepo= $currencyExchangeRepo;
    }
    
    /**
     * @return bool
     * @throws \ErrorException
     */
    public function isCurrentCurrencyDefault()
    {
        return $this->getCurrentCurrency() == $this->getDefaultCurrency();
    }
    
    /**
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->currencyExchcangeRepo->getDefaultCurrency();
    }
    
    /**
     * @return string
     * @throws \ErrorException
     */
    public function getCurrentCurrency()
    {
        /** @var CheckoutService $checkoutService */
        $checkoutService = pluginApp(CheckoutService::class);
        return $checkoutService->getCurrency();
    }
    
    /**
     * @param float $amount
     * @return float
     * @throws \ErrorException
     */
    public function convertToDefaultCurrency($amount)
    {
        if(!$this->isCurrentCurrencyDefault())
        {
            return $this->currencyExchcangeRepo->convertToDefaultCurrency($this->getCurrentCurrency(), $amount);
        }
        
        return $amount;
    }
}