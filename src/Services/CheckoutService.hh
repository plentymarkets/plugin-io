<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use LayoutCore\Constants\SessionStorageKeys;

class CheckoutService
{
    private FrontendPaymentMethodRepositoryContract $paymentMethodRepository;
    private Checkout $checkout;
    private BasketRepositoryContract $basketRepository;
    private FrontendSessionStorageFactoryContract $sessionStorage;

    public function __construct(
        FrontendPaymentMethodRepositoryContract $paymentMethodRepository,
        Checkout $checkout,
        BasketRepositoryContract $basketRepository,
        FrontendSessionStorageFactoryContract $sessionStorage )
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->checkout = $checkout;
        $this->basketRepository = $basketRepository;
        $this->sessionStorage = $sessionStorage;
    }

    public function getCheckout(): array<string, mixed>
    {
        return [
            "currency" => $this->getCurrency(),
            "methodOfPaymentId" => $this->getMethodOfPaymentId(),
            "methodOfPaymentList" => $this->getMethodOfPaymentList(),
            "shippingCountryId" => $this->getShippingCountryId(),
            "shippingProfileId" => $this->getShippingProfileId(),
            "deliveryAddressId" => $this->getDeliveryAddressId(),
            "billingAddressId" => $this->getBillingAddressId(),
        ];
    }

    public function getCurrency():string
    {
        $currency = (string) $this->sessionStorage->getPlugin()->getValue( SessionStorageKeys::CURRENCY );
        if( $currency === null || $currency === "" )
        {
            $currency = "EUR";
            $this->setCurrency( $currency );
        }
        return $currency;
    }

    public function setCurrency( string $currency ): void
    {
        $this->sessionStorage->getPlugin()->setValue( SessionStorageKeys::CURRENCY, $currency );
    }

    public function getMethodOfPaymentId():int
    {
        foreach( $this->getMethodOfPaymentList() as $payment )
        {
            if( $payment->active )
            {
                return $payment->id;
            }
        }
        return -1;
    }

    public function setMethodOfPaymentId( int $methodOfPaymentID ):void
    {
        $this->checkout->setPaymentMethodId( $methodOfPaymentID );
    }

    public function getMethodOfPaymentList():array<PaymentMethod>
    {
        return $this->paymentMethodRepository->getCurrentPaymentMethodsList();
    }

    public function getShippingCountryId():int
    {
        $basket = $this->basketRepository->load();
        return $basket->shippingCountryId;
    }

    public function setShippingCountryId( int $shippingCountryId ):void
    {
        $this->checkout->setShippingCountryId( $shippingCountryId );
    }

    public function getShippingProfileId():int
    {
        $basket = $this->basketRepository->load();
        return $basket->shippingProfileId;
    }

    public function setShippingProfileId( int $shippingProfileId ):void
    {
        $this->checkout->setShippingProfileId( $shippingProfileId );
    }

    public function getDeliveryAddressId():int
    {
        return (int) $this->sessionStorage->getPlugin()->getValue( SessionStorageKeys::DELIVERY_ADDRESS_ID );
    }

    public function setDeliveryAddressId( int $deliveryAddressId ): void
    {
        $this->sessionStorage->getPlugin()->setValue( SessionStorageKeys::DELIVERY_ADDRESS_ID, $deliveryAddressId );
    }

    public function getBillingAddressId(): int
    {
        return (int) $this->sessionStorage->getPlugin()->getValue( SessionStorageKeys::BILLING_ADDRESS_ID );
    }

    public function setBillingAddressId( int $billingAddressId ): void
    {
        $this->sessionStorage->getPlugin()->setValue( SessionStorageKeys::BILLING_ADDRESS_ID, $billingAddressId );
        if( $this->getDeliveryAddressId() <= 0 )
        {
            $this->setDeliveryAddressId( $billingAddressId );
        }
    }
}
