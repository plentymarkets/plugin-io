<?php

namespace IO\Helper;

use Plenty\Modules\Frontend\Contracts\CurrencyExchangeRepositoryContract;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;

/**
 * Class CurrencyConverter
 *
 * This class was used to convert a non-default currency to the default currency.
 * @package IO\Helper
 *
 * @deprecated since 5.0.0 will be removed in 6.0.0.
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
     * Check whether the current currency is the default currency.
     * @return bool
     * @throws \ErrorException
     */
    public function isCurrentCurrencyDefault()
    {
        return $this->getCurrentCurrency() == $this->getDefaultCurrency();
    }

    /**
     * Get the default currency.
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->currencyExchcangeRepo->getDefaultCurrency();
    }

    /**
     * Get the current currency.
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
     * If current currency is not the default, convert an amount to the default currency.
     * @param float $amount An amount of currency.
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
