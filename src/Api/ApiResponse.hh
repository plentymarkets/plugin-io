<?hh //strict

namespace LayoutCore\Api;

use Illuminate\Http\Response;
use Plenty\Plugin\Events\Dispatcher;

enum ResponseCode:int
{
    CONTINUE                        = 100;
    SWITCHING_PROTOCOLS             = 101;
    PROCESSING                      = 102; // RFC2518

    OK                              = 200;
    CREATED                         = 201;
    ACCEPTED                        = 202;
    NON_AUTHORITATIVE_INFORMATION   = 203;
    NO_CONTENT                      = 204;
    RESET_CONTENT                   = 205;
    PARTIAL_CONTENT                 = 206;
    MULTI_STATUS                    = 207; // RFC4918
    ALREADY_REPORTED                = 208; // RFC5842
    IM_USED                         = 226; // RFC3229

    MULTIPLE_CHOICES                = 300;
    MOVED_PERMANENTLY               = 301;
    FOUND                           = 302;
    SEE_OTHER                       = 303;
    NOT_MODIFIED                    = 304;
    USE_PROXY                       = 305;
    RESERVED                        = 306;
    TEMPORARY_REDIRECT              = 307;
    PERMANENTLY_REDIRECT            = 308; // RFC7238

    BAD_REQUEST                     = 400;
    UNAUTHORIZED                    = 401;
    PAYMENT_REQUIRED                = 402;
    FORBIDDEN                       = 403;
    NOT_FOUND                       = 404;
    METHOD_NOT_ALLOWED              = 405;
    NOT_ACCEPTABLE                  = 406;
    PROXY_AUTHENTICATION_REQUIRED   = 407;
    REQUEST_TIMEOUT                 = 408;
    CONFLICT                        = 409;
    GONE                            = 410;
    LENGTH_REQUIRED                 = 411;
    PRECONDITION_FAILED             = 412;
    REQUEST_ENTITY_TOO_LARGE        = 413;
    REQUEST_URI_TOO_LONG            = 414;
    UNSUPPORTED_MEDIA_TYPE          = 415;
    REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    EXPECTATION_FAILED              = 417;
    I_AM_A_TEAPOT                   = 418; // RFC2324
    UNPROCESSABLE_ENTITY            = 422; // RFC4918
    LOCKED                          = 423; // RFC4918
    FAILED_DEPENDENCY               = 424; // RFC4918
    RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425; // RFC2817
    UPGRADE_REQUIRED                = 426; // RFC2817
    PRECONDITION_REQUIRED           = 428; // RFC6585
    TOO_MANY_REQUESTS               = 429; // RFC6585
    REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585
    UNAVAILABLE_FOR_LEGAL_REASONS   = 451;

    INTERNAL_SERVER_ERROR           = 500;
    NOT_IMPLEMENTED                 = 501;
    BAD_GATEWAY                     = 502;
    SERVICE_UNAVAILABLE             = 503;
    GATEWAY_TIMEOUT                 = 504;
    VERSION_NOT_SUPPORTED           = 505;
    VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295
    INSUFFICIENT_STORAGE            = 507; // RFC4918
    LOOP_DETECTED                   = 508; // RFC5842
    NOT_EXTENDED                    = 510; // RFC2774
    NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC6585
}

class ApiResponse
{
    private Dispatcher $dispatcher;
    private array<string, mixed> $eventData = array();
    private mixed $data = null;
    private array<string, ?array<string, mixed>> $notifications = [
        "error"     => null,
        "success"   => null,
        "info"      => null
    ];
    private array<string, string> $headers = array();

    public function __construct( Dispatcher $dispatcher )
    {
        $this->dispatcher = $dispatcher;
        // register basket Events
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\Basket\AfterBasketChanged::class, ($event) ==> {
            $this->eventData["AfterBasketChanged"] = [
                "basket" => $event->getBasket()
            ];
        });

        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\Basket\AfterBasketCreate::class, ($event) ==> {
            $this->eventData["AfterBasketCreate"] = [
                "basket" => $event->getBasket()
            ];
        });

        // register Basket Item Events
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemAdd::class, ($event) ==> {
            $this->eventData["BeforeBasketItemAdd"] = [
                "basketItem" => $event->getBasketItem()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd::class, ($event) ==> {
            $this->eventData["AfterBasketItemAdd"] = [
                "basketItem" => $event->getBasketItem()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemRemove::class, ($event) ==> {
            $this->eventData["BeforeBasketItemRemove"] = [];
        });
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemRemove::class, ($event) ==> {
            $this->eventData["AfterBasketItemRemove"] = [];
        });
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemUpdate::class, ($event) ==> {
            $this->eventData["BeforeBasketItemUpdate"] = [];
        });
        $this->dispatcher->listen( \Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemUpdate::class, ($event) ==> {
            $this->eventData["AfterBasketItemUpdate"] = [];
        });

        // register Frontend Events
        $this->dispatcher->listen( \Plenty\Modules\Frontend\Events\FrontendCurrencyChanged::class, ($event) ==> {
            $this->eventData["FrontendCurrencyChanged"] = [
                "curency" => $event->getCurrency(),
                "exchangeRatio" => $event->getCurrencyExchangeRatio()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Frontend\Events\FrontendLanguageChanged::class, ($event) ==> {
            $this->eventData["FrontendLanguageChanged"] = [
                "language" => $event->getLanguage()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress::class, ($event) ==> {
            $this->eventData["FrontendUpdateDeliveryAddress"] = [
                "accountAddressId" => $event->getAccountAddressId()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Frontend\Events\FrontendUpdateShippingSettings::class, ($event) ==> {
            $this->eventData["FrontendUpdateShippingSettings"] = [
                "shippingCosts" => $event->getShippingCosts(),
                "parcelServiceId" => $event->getParcelServiceId(),
                "parcelServicePresetId" => $event->getParcelServicePresetId()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Account\Events\FrontendUpdateCustomerSettings::class, ($event) ==> {
            $this->eventData["FrontendUpdateCustomerSettings"] = [
                "deliveryCountryId" => $event->getDeliveryCountryId(),
                "showNetPrice" => $event->getShowNetPrice(),
                "ebaySellerAccount" => $event->getEbaySellerAccount(),
                "accountContactSign" => $event->getAccountContactSign(),
                "accountContactClassId" => $event->getAccountContactClassId(),
                "salesAgent" => $event->getSalesAgent(),
                "accountContractClassId" => $event->getAccountContractClassId()
            ];
        });
        $this->dispatcher->listen( \Plenty\Modules\Frontend\Events\FrontendUpdatePaymentSettings::class, ($event) ==> {
            $this->eventData["FrontendUpdatePaymentSettings"] = [
                "paymentMethodId" => $event->getPaymentMethodId()
            ];
        });

        // register Auth Events
		$this->dispatcher->listen( \Plenty\Modules\Authentication\Events\AfterAccountAuthentication::class, ($event) ==> {
			$this->eventData["AfterAccountAuthentication"] = [
				"isSuccess" => $event->isSuccessful(),
				"accountContact" => $event->getAccountContact()
			];
		});
		$this->dispatcher->listen( \Plenty\Modules\Authentication\Events\AfterAccountContactLogout::class, ($event) ==> {
			$this->eventData["AfterAccountContactLogout"] = [];
		});
    }

    public function error( int $code, ?string $message = null ):ApiResponse
    {
        $this->pushNotification( "error", $code, $message );
        return $this;
    }

    public function success( int $code, ?string $message = null ):ApiResponse
    {
        $this->pushNotification( "success", $code, $message );
        return $this;
    }

    public function info( int $code, ?string $message = null ):ApiResponse
    {
        $this->pushNotification( "info", $code, $message );
        return $this;
    }

    private function pushNotification( string $context, int $code, ?string $message = null ):ApiResponse
    {
        if( $message === null )
        {
            // TODO: get error message from system config
            $message = "";
        }

        $notification = [
            "code" => $code,
            "message" => $message,
            "stackTrace" => array()
        ];

        $head = $this->notifications[$context];
        if( $head !== null )
        {
            $notification["stackTrace"] = $head["stackTrace"];
            $head["stackTrace"] = array();
            array_push( $notification["stackTrace"], $head );
        }

        $this->notifications[$context] = $notification;
        return $this;
    }

    public function header( string $key, string $value ):ApiResponse
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function create( mixed $data, ResponseCode $code = ResponseCode::OK, array<string, string> $headers = array() ):Response
    {
        foreach( $headers as $key => $value )
        {
            $this->header( $key, $value );
        }

        $responseData = [];
        if( $this->notifications["error"] !== null )
        {
            $responseData["error"] = $this->notifications["error"];
        }

        if( $this->notifications["success"] !== null )
        {
            $responseData["success"] = $this->notifications["success"];
        }

        if( $this->notifications["info"] !== null )
        {
            $responseData["info"] = $this->notifications["info"];
        }

        $responseData["events"] = $this->eventData;
        $responseData["data"] = $data;

        return Response::create($responseData, $code, $this->headers);
    }
}
