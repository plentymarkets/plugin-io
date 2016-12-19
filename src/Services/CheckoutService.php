<?php //strict

namespace IO\Services;

use IO\Builder\Order\AddressType;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use IO\Constants\SessionStorageKeys;

/**
 * Class CheckoutService
 * @package IO\Services
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
     * @var CustomerService
     */
    private $customerService;

    /**
     * CheckoutService constructor.
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository
     * @param Checkout $checkout
     * @param BasketRepositoryContract $basketRepository
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     * @param CustomerService $customerService
     */
	public function __construct(
		FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepository,
		Checkout $checkout,
		BasketRepositoryContract $basketRepository,
		FrontendSessionStorageFactoryContract $sessionStorage,
        CustomerService $customerService)
	{
		$this->frontendPaymentMethodRepository = $frontendPaymentMethodRepository;
		$this->checkout                      = $checkout;
		$this->basketRepository              = $basketRepository;
		$this->sessionStorage                = $sessionStorage;
        $this->customerService               = $customerService;
	}

    /**
     * Get the relevant data for the checkout
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
            "shippingProfileList" => $this->getShippingProfileList(),
			"deliveryAddressId"   => $this->getDeliveryAddressId(),
			"billingAddressId"    => $this->getBillingAddressId(),
			"paymentDataList"     => $this->getCheckoutPaymentDataList(),
		];
	}

    /**
     * Get the current currency from the session
     * @return string
     */
	public function getCurrency():string
	{
		$currency = (string)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::CURRENCY);
		if($currency === null || $currency === "")
		{
            /** @var WebstoreConfigurationService $webstoreConfig */
            $webstoreConfig = pluginApp( WebstoreConfigurationService::class );
			$currency = $webstoreConfig->getWebstoreConfig()->defaultCurrency;
			$this->setCurrency($currency);
		}
		return $currency;
	}

    /**
     * Set the current currency from the session
     * @param string $currency
     */
	public function setCurrency(string $currency)
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::CURRENCY, $currency);
	}

    /**
     * Get the ID of the current payment method
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
     * Set the ID of the current payment method
     * @param int $methodOfPaymentID
     */
	public function setMethodOfPaymentId(int $methodOfPaymentID)
	{
		$this->checkout->setPaymentMethodId($methodOfPaymentID);
		$this->sessionStorage->getPlugin()->setValue( 'MethodOfPaymentID', $methodOfPaymentID );
	}

    /**
     * Prepare the payment
     * @return array
     */
	public function preparePayment():array
	{
		$mopId = $this->getMethodOfPaymentId();
		return pluginApp(PaymentMethodRepositoryContract::class)->preparePaymentMethod($mopId);
	}

    /**
     * List all available payment methods
     * @return array
     */
	public function getMethodOfPaymentList():array
	{
		return $this->frontendPaymentMethodRepository->getCurrentPaymentMethodsList();
	}

    /**
     * Get a list of the payment method data
     * @return array
     */
	public function getCheckoutPaymentDataList():array
    {
        $paymentDataList = array();
        $mopList = $this->getMethodOfPaymentList();
        $lang = pluginApp(SessionStorageService::class)->getLang();
        foreach($mopList as $paymentMethod)
        {
            $paymentData = array();
            $paymentData['id'] = $paymentMethod->id;
            $paymentData['name'] = $this->frontendPaymentMethodRepository->getPaymentMethodName($paymentMethod, $lang);
            $paymentData['fee'] = $this->frontendPaymentMethodRepository->getPaymentMethodFee($paymentMethod);
            $paymentData['icon'] = $this->frontendPaymentMethodRepository->getPaymentMethodIcon($paymentMethod, $lang);
            $paymentData['description'] = $this->frontendPaymentMethodRepository->getPaymentMethodDescription($paymentMethod, $lang);
            $paymentDataList[] = $paymentData;
        }
        return $paymentDataList;
    }

    /**
     * Get the shipping profile list
     * @return array
     */
    public function getShippingProfileList()
    {
        $contact = $this->customerService->getContact();
        return pluginApp(ParcelServicePresetRepositoryContract::class)->getLastWeightedPresetCombinations($this->basketRepository->load(), $contact->classId);
    }

    /**
     * Get the ID of the current shipping country
     * @return int
     */
	public function getShippingCountryId()
	{
		return $this->checkout->getShippingCountryId();
	}

    /**
     * Set the ID of thevcurrent shipping country
     * @param int $shippingCountryId
     */
	public function setShippingCountryId(int $shippingCountryId)
	{
		$this->checkout->setShippingCountryId($shippingCountryId);
	}

    /**
     * Get the ID of the current shipping profile
     * @return int
     */
	public function getShippingProfileId():int
	{
		$basket = $this->basketRepository->load();
		return $basket->shippingProfileId;
	}

    /**
     * Set the ID of the current shipping profile
     * @param int $shippingProfileId
     */
	public function setShippingProfileId(int $shippingProfileId)
	{
		$this->checkout->setShippingProfileId($shippingProfileId);
	}

    /**
     * Get the ID of the current delivery address
     * @return int
     */
	public function getDeliveryAddressId()
	{
		return (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::DELIVERY_ADDRESS_ID);
	}

    /**
     * Set the ID of the current delivery address
     * @param int $deliveryAddressId
     */
	public function setDeliveryAddressId($deliveryAddressId)
	{
		$this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, $deliveryAddressId);
	}

    /**
     * Get the ID of the current invoice address
     * @return int
     */
    public function getBillingAddressId()
    {
        $billingAddressId = (int)$this->sessionStorage->getPlugin()->getValue(SessionStorageKeys::BILLING_ADDRESS_ID);

        if ($billingAddressId === 0)
        {
            $addresses = $this->customerService->getAddresses(AddressType::BILLING);
            if (count($addresses) > 0)
            {
                $billingAddressId = $addresses[0]->id;
                $this->setBillingAddressId($billingAddressId);
            }
        }

        return $billingAddressId;
    }

    /**
     * Set the ID of the current invoice address
     * @param int $billingAddressId
     */
	public function setBillingAddressId($billingAddressId)
	{
        if((int)$billingAddressId > 0)
        {
            $this->sessionStorage->getPlugin()->setValue(SessionStorageKeys::BILLING_ADDRESS_ID, $billingAddressId);
        }
	}
}
