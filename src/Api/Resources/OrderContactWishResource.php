<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;

/**
 * Class OrderContactWishResource
 * @package IO\Api\Resources
 */
class OrderContactWishResource extends ApiResource
{
    /**
     * OrderContactWishResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    public function store():Response
    {
        $orderContactWish = $this->request->get('orderContactWish', '');
        
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);
        
        if(strlen($orderContactWish))
        {
            $sessionStorage->setSessionValue(SessionStorageKeys::ORDER_CONTACT_WISH, $orderContactWish);
        }
        
        return $this->response->create($orderContactWish, ResponseCode::CREATED );
    }
}
