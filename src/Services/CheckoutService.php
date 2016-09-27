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

	public function setCurrency(string $currency): void
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $currency);
	}

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

	public function setMethodOfPaymentId(int $methodOfPaymentID)
	{
		$this->checkout->setPaymentMethodId($methodOfPaymentID);
		$this->sessionStorage->getPlugin()->setValue( 'MethodOfPaymentID', $methodOfPaymentID );
	}

	public function preparePayment():array
	{
		$mopId = $this->getMethodOfPaymentId();
		return $this->paymentMethodRepository->preparePaymentMethod($mopId);
	}

	public function getMethodOfPaymentList():array
	{
		return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsList();
	}

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

	public function getShippingCountryId():int
	{
		$basket = $this->basketRepository->load();
		return $basket->shippingCountryId;
	}

	public function setShippingCountryId(int $shippingCountryId)
	{
		$this->checkout->setShippingCountryId($shippingCountryId);
	}

	public function getShippingProfileId():int
	{
		$basket = $this->basketRepository->load();
		return $basket->shippingProfileId;
	}

	public function setShippingProfileId(int $shippingProfileId)
	{
		$this->checkout->setShippingProfileId($shippingProfileId);
	}

	public function getDeliveryAddressId():int
	{
		return (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::DELIVERY_ADDRESS_ID);
	}

	public function setDeliveryAddressId(int $deliveryAddressId): void
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, $deliveryAddressId);
	}

	public function getBillingAddressId(): int
	{
		return (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::BILLING_ADDRESS_ID);
	}

	public function setBillingAddressId(int $billingAddressId): void
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::BILLING_ADDRESS_ID, $billingAddressId);
		if($this->getDeliveryAddressId() <= 0)
		{
			$this->setDeliveryAddressId($billingAddressId);
		}
	}
}
