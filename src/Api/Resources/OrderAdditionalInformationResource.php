<?php //strict

namespace IO\Api\Resources;

use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class OrderAdditionalInformationResource
 * @package IO\Api\Resources
 */
class OrderAdditionalInformationResource extends ApiResource
{

    private $sessionStorage;
    
    public function __construct(Request $request, ApiResponse $response, SessionStorageService $sessionStorage)
    {
        parent::__construct($request, $response);
        $this->sessionStorage = $sessionStorage;
    }
    
    public function store():Response
    {
        $this->setContactWish();
        $this->setShippingPrivacyHint();

        return $this->response->create('', ResponseCode::CREATED );
    }

    private function setContactWish()
    {
        $orderContactWish = $this->request->get('orderContactWish', '');

        if(strlen($orderContactWish))
        {
            $this->sessionStorage->setSessionValue(SessionStorageKeys::ORDER_CONTACT_WISH, $orderContactWish);
        }
    }

    private function setShippingPrivacyHint()
    {
        $this->sessionStorage->setSessionValue(
            SessionStorageKeys::SHIPPING_PRIVACY_HINT_ACCEPTED,
            $this->request->get('shippingPrivacyHintAccepted', 'false'));
    }
}
