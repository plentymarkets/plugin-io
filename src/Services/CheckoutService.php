<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use LayoutCore\Constants\SessionStorageKeys;

/**
 * Class CheckoutService
 * @package LayoutCore\Services
 */
class CheckoutService
{
	/**
	 * @var FrontendPaymentMethodRepositoryContract
	 */
	private $frontendPaymentMethodRepository;
	/**
	 * @var Checkout
	 */
	private $checkout;
	/**
	 * @var BasketRepositoryContract
	 */
	private $basketRepository;
	/**
	 * @var FrontendSessionStorageFactoryContract
	 */
	private $sessionStorage;

	/**
	 * @var PaymentMethodRepositoryContract
	 */
	private $paymentMethodRepository;
    
    /**
     * CheckoutService constructor.
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
     * @param Checkout $checkout
     * @param BasketRepositoryContract $basketRepository
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     */
	public function __construct(
		FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository,
		Checkout $checkout,
		BasketRepositoryContract $basketRepository,
		FrontendSessionStorageFactoryContract $sessionStorage,
		PaymentMethodRepositoryContract $paymentMethodRepository )
	{
		$this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
		$this->checkout                = $checkout;
		$this->basketRepository        = $basketRepository;
		$this->sessionStorage          = $sessionStorage;
		$this->paymentMethodRepository = $paymentMethodRepository;
	}
    
    /**
     * get relevant data for checkout
     * @return array
     */
	public function getCheckout(): array
	{
		return [
			"currency"            => $this->getCurrency(),
			"methodOfPaymentId"   => $this->getMethodOfPaymentId(),
			"methodOfPaymentList" => $this->getMethodOfPaymentList(),
			"shippingCountryId"   => $this->getShippingCountryId(),
			"shippingProfileId"   => $this->getShippingProfileId(),
			"deliveryAddressId"   => $this->getDeliveryAddressId(),
			"billingAddressId"    => $this->getBillingAddressId(),
		];
	}
    
    /**
     * get the current currency from session
     * @return string
     */
	public function getCurrency():string
	{
		$currency = (string)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::CURRENCY);
		if($currency === null || $currency === "")
		{
			$currency = "EUR";
			$this->setCurrency($currency);
		}
		return $currency;
	}
    
    /**
     * set the current currency from session
     * @param string $currency
     */
	public function setCurrency(string $currency)
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $currency);
	}
    
    /**
     * get id of the current payment method
     * @return int
     */
	public function getMethodOfPaymentId():int
	{
		$methodOfPaymentID = (int)$this->sessionStorage->getPlugin()->getValue( 'MethodOfPaymentID' );
        if( $methodOfPaymentID === null )
        {
            $methodOfPaymentList = $this->getMethodOfPaymentList();
            $methodOfPaymentID = $methodOfPaymentList[0]->id;
            $this->setMethodOfPaymentId($methodOfPaymentID);
        }
        return $methodOfPaymentID;
	}
    
    /**
     * set id of the current payment method
     * @param int $methodOfPaymentID
     */
	public function setMethodOfPaymentId(int $methodOfPaymentID)
	{
		$this->checkout->setPaymentMethodId($methodOfPaymentID);
		$this->sessionStorage->getPlugin()->setValue( 'MethodOfPaymentID', $methodOfPaymentID );
	}
    
    /**
     * prepare the payment
     * @return array
     */
	public function preparePayment():array
	{
		$mopId = $this->getMethodOfPaymentId();
		return $this->paymentMethodRepository->preparePaymentMethod($mopId);
	}
    
    /**
     * get list of available payment methods
     * @return array
     */
	public function getMethodOfPaymentList():array
	{
		return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsList();
	}
    
    /**
     * get a list of payment method data
     * @return array
     */
	public function getCheckoutPaymentDataList():array
    {
        $paymentDataList = array();
        $mopList = $this->getMethodOfPaymentList();
        foreach($mopList as $paymentMethod)
        {
            $paymentData = array();
            $paymentData['id'] = $paymentMethod->id;
            $paymentData['name'] = $this->frontendPaymentMethodRepository->getPaymentMethodName($paymentMethod);
            $paymentData['fee'] = $this->frontendPaymentMethodRepository->getPaymentMethodFee($paymentMethod);
            $paymentData['icon'] = $this->frontendPaymentMethodRepository->getPaymentMethodIcon($paymentMethod);
            $paymentData['description'] = $this->frontendPaymentMethodRepository->getPaymentMethodDescription($paymentMethod);
            $paymentDataList[] = $paymentData;
        }
        return $paymentDataList;
    }
    
    /**
     * get the current shipping country id
     * @return int
     */
	public function getShippingCountryId():int
	{
		$basket = $this->basketRepository->load();
		return $basket->shippingCountryId;
	}
    
    /**
     * set the current shipping country id
     * @param int $shippingCountryId
     */
	public function setShippingCountryId(int $shippingCountryId)
	{
		$this->checkout->setShippingCountryId($shippingCountryId);
	}
    
    /**
     * get the current shipping profile id
     * @return int
     */
	public function getShippingProfileId():int
	{
		$basket = $this->basketRepository->load();
		return $basket->shippingProfileId;
	}
    
    /**
     * set the current shipping profile id
     * @param int $shippingProfileId
     */
	public function setShippingProfileId(int $shippingProfileId)
	{
		$this->checkout->setShippingProfileId($shippingProfileId);
	}
    
    /**
     * get the current delivery address id
     * @return int
     */
	public function getDeliveryAddressId():int
	{
		return (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::DELIVERY_ADDRESS_ID);
	}
    
    /**
     * set the current delivery address id
     * @param int $deliveryAddressId
     */
	public function setDeliveryAddressId(int $deliveryAddressId)
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, $deliveryAddressId);
	}
    
    /**
     * get the current billing address id
     * @return int
     */
	public function getBillingAddressId(): int
	{
		return (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::BILLING_ADDRESS_ID);
	}
    
    /**
     * set the current billing address id
     * @param int $billingAddressId
     */
	public function setBillingAddressId(int $billingAddressId)
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::BILLING_ADDRESS_ID, $billingAddressId);
		if($this->getDeliveryAddressId() <= 0)
		{
			$this->setDeliveryAddressId($billingAddressId);
		}
	}
}
