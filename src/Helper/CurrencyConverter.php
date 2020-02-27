<?php

namespace IO\Helper;

use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;

/**
 * Class CurrencyConverter
 *
 * @package IO\Helper
 *
 * @depreacted since 5.0.0 will be removed in 6.0.0
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
        /** @var  CheckoutRepositoryContract $checkoutRepository */
        $checkoutRepository = pluginApp(CheckoutRepositoryContract::class);
        return $checkoutRepository->getCurrency();
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
