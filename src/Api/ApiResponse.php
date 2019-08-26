<?php //strict

namespace IO\Api;

use IO\Constants\LogLevel;
use IO\Services\BasketService;
use IO\Services\CheckoutService;
use IO\Services\LocalizationService;
use IO\Services\NotificationService;
use Plenty\Modules\Item\Stock\Events\BasketItemWarnOversell;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Account\Events\FrontendUpdateCustomerSettings;
use Plenty\Modules\Authentication\Events\AfterAccountAuthentication;
use Plenty\Modules\Authentication\Events\AfterAccountContactLogout;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemRemove;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemUpdate;
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
use IO\Services\CustomerService;

/**
 * Class ApiResponse
 * @package IO\Api
 */
class ApiResponse
{
	/**
	 * @var array
	 */
	public $eventData = [];

	/**
	 * @var Dispatcher
	 */
	private $dispatcher;

	/**
	 * @var mixed
	 */
	private $data = null;

	/**
	 * @var array
	 */
	private $headers = [];
    
    /**
     * @var null|Response
     */
    private $response = null;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * ApiResponse constructor.
     * @param Dispatcher $dispatcher
     * @param Response $response
     * @param NotificationService $notificationService
     */
	public function __construct(
	    Dispatcher $dispatcher,
        Response $response,
        NotificationService $notificationService)
	{
		$this->dispatcher           = $dispatcher;
        $this->response             = $response;
        $this->notificationService  = $notificationService;

		// Register basket events
        $this->dispatcher->listen( AfterBasketChanged::class, function($event) {
            // FIX: Set basket and checkout data after "showNetPrice" has been recalculated
            // showNetPrice does not have been recalculated at this point
            $this->eventData["AfterBasketChanged"] = [
				"basket" => null
            ];
            $this->eventData['CheckoutChanged'] = [
                'checkout' => null
            ];
        }, 0);

        $this->dispatcher->listen( AfterBasketCreate::class, function($event) {
            $this->eventData["AfterBasketCreate"] = [
                "basket" => $event->getBasket()
            ];
        }, 0);

		$this->dispatcher->listen(AfterBasketItemAdd::class, function ($event)
		{
		    $basketItem = $event->getBasketItem();
			$this->eventData["AfterBasketItemAdd"] = [
				"basketItem" => pluginApp(BasketService::class)->getBasketItem($basketItem)
			];
		}, 0);

		$this->dispatcher->listen(AfterBasketItemRemove::class, function ()
		{
			$this->eventData["AfterBasketItemRemove"] = [];
		}, 0);

		$this->dispatcher->listen(AfterBasketItemUpdate::class, function ($event)
		{
            $basketItem = $event->getBasketItem();
			$this->eventData["AfterBasketItemUpdate"] = [
			    "basketItem" => pluginApp(BasketService::class)->getBasketItem($basketItem, false)
            ];
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
            $this->eventData["LocalizationChanged"] = [
                "localization" => pluginApp(LocalizationService::class)->getLocalizationData()
            ];

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

		$this->dispatcher->listen(BasketItemWarnOversell::class, function ($event)
		{
			$stock = $event->getQuantity();
            $quantity = $event->getBasketItem()->quantity;
			$oversellingAmount = $quantity - $stock;
			$oversellingData = [
				'stock' => $stock,
				'quantity' => $quantity,
				'oversellingAmount' => $oversellingAmount
			];

			$this->eventData["BasketItemWarnOversell"] = $oversellingData;
            $this->notificationService->warn("Overselling by {$oversellingAmount}.", 12, $oversellingData);
		}, 0);
	}

    /**
     * @deprecated
     *
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function error(int $code, $message = null):ApiResponse
	{
		$this->notificationService->error( $message, $code );
		return $this;
	}

    /**
     * @deprecated
     *
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function success(int $code, $message = null):ApiResponse
	{
		$this->notificationService->success( $message, $code );
		return $this;
	}

    /**
     * @deprecated
     *
     * @param int $code
     * @param null $message
     * @return ApiResponse
     */
	public function info(int $code, $message = null):ApiResponse
	{
		$this->notificationService->info( $message, $code );
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
	 * @return Response
	 */
	public function create($data, int $code = ResponseCode::OK, array $headers = []):Response
	{
		foreach($headers as $key => $value)
		{
			$this->header($key, $value);
		}

		$responseData = $this->appendNotifications();

		$responseData["events"] = $this->eventData;

		// FIX: Set basket data after "showNetPrice" has been recalculated
        if ( array_key_exists('AfterBasketChanged', $responseData['events'] ) )
        {
            $responseData['events']['AfterBasketChanged']['basket']  = pluginApp(BasketService::class)->getBasketForTemplate();
            $responseData['events']['AfterBasketChanged']['showNetPrices']  = pluginApp(CustomerService::class)->showNetPrices();
            $responseData['events']['CheckoutChanged']['checkout']   = pluginApp(CheckoutService::class)->getCheckout();
        }

		$responseData["data"]   = $data;

        return $this->response->make(json_encode($responseData), $code, $this->headers);
	}

	private function appendNotifications( $data = null, $type = null, $notifications = null )
    {
        if ( is_null($data) )
        {
            $data = [];
        }

        if ( is_null($notifications) )
        {
            $notifications = $this->notificationService->getNotifications();
        }

        if ( !is_null($notifications[LogLevel::ERROR]) )
        {
            $data[LogLevel::ERROR] = $notifications[LogLevel::ERROR];
        }

        if ( !is_null($notifications[LogLevel::WARN]) )
        {
            $data[LogLevel::WARN] = $notifications[LogLevel::WARN];
        }

        if ( !is_null($notifications[LogLevel::INFO]) )
        {
            $data[LogLevel::INFO] = $notifications[LogLevel::INFO];
        }

        if ( !is_null($notifications[LogLevel::SUCCESS]) )
        {
            $data[LogLevel::SUCCESS] = $notifications[LogLevel::SUCCESS];
        }

        if ( !is_null($notifications[LogLevel::ERROR]) )
        {
            $data[LogLevel::LOG] = $notifications[LogLevel::LOG];
        }

        return $data;
    }
}
