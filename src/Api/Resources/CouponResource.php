<?php //strict

namespace IO\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CouponService;

class CouponResource extends ApiResource
{
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    public function index():BaseResponse
    {
        return $this->response->create([], ResponseCode::OK);
    }
    
    public function store():BaseResponse
    {
        $couponCode = $this->request->get('couponCode', '');
        
        $couponService = pluginApp(CouponService::class);
        $response = $couponService->setCoupon($couponCode);
        
        return $this->response->create( $response, ResponseCode::CREATED );
    }
}