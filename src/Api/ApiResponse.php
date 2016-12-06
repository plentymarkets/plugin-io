<?php //strict

namespace IO\Api;

use IO\Services\BasketService;
use IO\Services\CheckoutService;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Account\Events\FrontendUpdateCustomerSettings;
use Plenty\Modules\Authentication\Events\AfterAccountAuthentication;
use Plenty\Modules\Authentication\Events\AfterAccountContactLogout;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemRemove;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemUpdate;
use Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemAdd;
use Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemRemove;
use Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemUpdate;
use Plenty\Modules\Frontend\Events\FrontendCurrencyChanged;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;
use Plenty\Modules\Frontend\Events\FrontendUpdatePaymentSettings;
use Plenty\Modules\Frontend\Events\FrontendUpdateShippingSettings;
use Plenty\Modules\Frontend\Events\FrontendPaymentMethodChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingProfileChanged;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class ApiResponse
 * @package IO\Api
 */
class ApiResponse
{
	/**
	 * @var Dispatcher
	 */
	private $dispatcher;
	/**
	 * @var array
	 */
	private $eventData = [];
	/**
	 * @var mixed
	 */
	private $data = null;

	private $notifications = [
		"error"   => null,
		"success" => null,
		"info"    => null
	];
	/**
	 * @var array
	 */
	private $headers = [];
    
    /**
     * @var null|Response
     */
    private $response = null;
    
    /**
     * ApiResponse constructor.
     * @param Dispatcher $dispatcher
     * @param Response $response
     */
	public function __construct(
	    Dispatcher $dispatcher,
        Response $response)
	{
		$this->dispatcher = $dispatcher;
        $this->response = $response;

		// Register basket events
        $this->dispatcher->listen( AfterBasketChanged::class, function($event) {
            $this->eventData["AfterBasketChanged"] = [
                "basket" => pluginApp(BasketService::class)->getBasket()
            ];
            $this->eventData['CheckoutChanged'] = [
                'checkout' => pluginApp(CheckoutService::class)->getCheckout()
            ];
        }, 0);

        $this->dispatcher->listen( AfterBasketCreate::class, function($event) {
            $this->eventData["AfterBasketCreate"] = [
                "basket" => $event->getBasket()
            ];
        }, 0);

		// Register events for basket items
		$this->dispatcher->listen(BeforeBasketItemAdd::class, function ($event)
		{
			$this->eventData["BeforeBasketItemAdd"] = [
				"basketItem" => $event->getBasketItem()
			];
		}, 0);
		$this->dispatcher->listen(AfterBasketItemAdd::class, function ($event)
		{
			$this->eventData["AfterBasketItemAdd"] = [
				"basketItem" => $event->getBasketItem()
			];
		}, 0);
		$this->dispatcher->listen(BeforeBasketItemRemove::class, function ()
		{
			$this->eventData["BeforeBasketItemRemove"] = [];
		}, 0);
		$this->dispatcher->listen(AfterBasketItemRemove::class, function ()
		{
			$this->eventData["AfterBasketItemRemove"] = [];
		}, 0);
		$this->dispatcher->listen(BeforeBasketItemUpdate::class, function ()
		{
			$this->eventData["BeforeBasketItemUpdate"] = [];
		}, 0);
		$this->dispatcher->listen(AfterBasketItemUpdate::class, function ()
		{
			$this->eventData["AfterBasketItemUpdate"] = [];
		}, 0);

		// Register front end events
		$this->dispatcher->listen(FrontendCurrencyChanged::class, function ($event)
		{
			$this->eventData["FrontendCurrencyChanged"] = [
				"curency"       => $event->getCurrency(),
				"exchangeRatio" => $event->getCurrencyExchangeRatio()
			];
		}, 0);
		$this->dispatcher->listen(FrontendLanguageChanged::class, function ($event)
		{
			$this->eventData["FrontendLanguageChanged"] = [
				"language" => $event->getLanguage()
			];
		}, 0);
		$this->dispatcher->listen(FrontendUpdateDeliveryAddress::class, function ($event)
		{
			$this->eventData["FrontendUpdateDeliveryAddress"] = [
				"accountAddressId" => $event->getAccountAddressId()
			];
		}, 0);
		$this->dispatcher->listen(FrontendUpdateShippingSettings::class, function ($event)
		{
			$this->eventData["FrontendUpdateShippingSettings"] = [
				"shippingCosts"         => $event->getShippingCosts(),
				"parcelServiceId"       => $event->getParcelServiceId(),
				"parcelServicePresetId" => $event->getParcelServicePresetId()
			];
		}, 0);
		$this->dispatcher->listen(FrontendUpdateCustomerSettings::class, function ($event)
		{
			$this->eventData["FrontendUpdateCustomerSettings"] = [
				"deliveryCountryId"      => $event->getDeliveryCountryId(),
				"showNetPrice"           => $event->getShowNetPrice(),
				"ebaySellerAccount"      => $event->getEbaySellerAccount(),
				"accountContactSign"     => $event->getAccountContactSign(),
				"accountContactClassId"  => $event->getAccountContactClassId(),
				"salesAgent"             => $event->getSalesAgent(),
				"accountContractClassId" => $event->getAccountContractClassId()
			];
		}, 0);
		$this->dispatcher->listen(FrontendUpdatePaymentSettings::class, function ($event)
		{
			$this->eventData["FrontendUpdatePaymentSettings"] = [
				"paymentMethodId" => $event->getPaymentMethodId()
			];
		}, 0);
        $this->dispatcher->listen(FrontendPaymentMethodChanged::class, function ($event)
        {
            $this->eventData["FrontendPaymentMethodChanged"] = [];
        }, 0);
        $this->dispatcher->listen(FrontendShippingProfileChanged::class, function ($event)
        {
            $this->eventData["FrontendShippingProfileChanged"] = [];
        }, 0);
        
		// Register auth events
		$this->dispatcher->listen(AfterAccountAuthentication::class, function ($event)
		{
			$this->eventData["AfterAccountAuthentication"] = [
				"isSuccess"      => $event->isSuccessful(),
				"accountContact" => $event->getAccountContact()
			];
		}, 0);
		$this->dispatcher->listen(AfterAccountContactLogout::class, function ()
		{
			$this->eventData["AfterAccountContactLogout"] = [];
		}, 0);
	}

    /**
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function error(int $code, $message = null):ApiResponse
	{
		$this->pushNotification("error", $code, $message);
		return $this;
	}

    /**
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function success(int $code, $message = null):ApiResponse
	{
		$this->pushNotification("success", $code, $message);
		return $this;
	}

    /**
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function info(int $code, $message = null):ApiResponse
	{
		$this->pushNotification("info", $code, $message);
		return $this;
	}

    /**
     * @param string $context
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	private function pushNotification(string $context, int $code, $message = null):ApiResponse
	{
		if($message === null)
		{
			// TODO: get error message from system config
			$message = "";
		}

		$notification = [
			"code"       => $code,
			"message"    => $message,
			"stackTrace" => []
		];

		$head = $this->notifications[$context];
		if($head !== null)
		{
			$notification["stackTrace"] = $head["stackTrace"];
			$head["stackTrace"]         = [];
			array_push($notification["stackTrace"], $head);
		}

		$this->notifications[$context] = $notification;
		return $this;
	}

    /**
     * @param string $key
     * @param string $value
     * @return ApiResponse
     */
	public function header(string $key, string $value):ApiResponse
	{
		$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * @param $data
	 * @param int $code
	 * @param array $headers
	 * @return BaseResponse
	 */
	public function create($data, int $code = ResponseCode::OK, array $headers = []):BaseResponse
	{
		foreach($headers as $key => $value)
		{
			$this->header($key, $value);
		}

		$responseData = [];
		if($this->notifications["error"] !== null)
		{
			$responseData["error"] = $this->notifications["error"];
		}

		if($this->notifications["success"] !== null)
		{
			$responseData["success"] = $this->notifications["success"];
		}

		if($this->notifications["info"] !== null)
		{
			$responseData["info"] = $this->notifications["info"];
		}

		$responseData["events"] = $this->eventData;
		$responseData["data"]   = $data;

        return $this->response->make(json_encode($responseData), $code, $this->headers);
	}
}
