<?php //strict

namespace LayoutCore\Api\Resources;

use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CheckoutService;
use Plenty\Plugin\Http\Response;

class CheckoutPaymentResource extends ApiResource
{
    /**
     * @var CheckoutService
     */
    private $checkoutService;
    
    public function __construct( Request $request, ApiResponse $response, CheckoutService $checkoutService )
    {
        parent::__construct( $request, $response );
        $this->checkoutService = $checkoutService;
    }

    /**
     * @return Response
     */
    public function store():Response
    {
        $event = $this->checkoutService->preparePayment();
        return $this->response->create( $event, ResponseCode::OK );
    }
}
