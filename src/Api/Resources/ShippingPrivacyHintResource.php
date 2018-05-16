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
 * Class ShippingPrivacyHintResource
 * @package IO\Api\Resources
 */
class ShippingPrivacyHintResource extends ApiResource
{
    
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    public function store():Response
    {
        $hintAccepted = $this->request->get('hintAccepted', "false");
        $sessionStorage = pluginApp(SessionStorageService::class);
        $sessionStorage->setSessionValue(SessionStorageKeys::SHIPPING_PRIVACY_HINT_ACCEPTED, $hintAccepted);

        return $this->response->create($hintAccepted, ResponseCode::CREATED );
    }
}
