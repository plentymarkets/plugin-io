<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutPaymentResource
 * @package LayoutCore\Api\Resources
 */
class CheckoutPaymentResource extends ApiResource
{
    /**
     * @var CheckoutService
     */
    private $checkoutService;

    /**
     * CheckoutPaymentResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CheckoutService $checkoutService
     */
    public function __construct( Request $request, ApiResponse $response, CheckoutService $checkoutService )
    {
        parent::__construct( $request, $response );
        $this->checkoutService = $checkoutService;
    }

    /**
     * Prepare the payment
     * @return BaseResponse
     */
    public function store():BaseResponse
    {
        $event = $this->checkoutService->preparePayment();
        return $this->response->create( $event, ResponseCode::OK );
    }
}
