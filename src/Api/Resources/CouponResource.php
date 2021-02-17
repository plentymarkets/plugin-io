<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CouponService;

/**
 * Class CouponResource
 *
 * Resource class for the route `io/coupon`.
 * @package IO\Api\Resources
 */
class CouponResource extends ApiResource
{
    /**
     * CouponResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Empty method.
     * @return Response
     */
    public function index():Response
    {
        return $this->response->create([], ResponseCode::OK);
    }

    /**
     * Redeem a coupon in the basket.
     * @return Response
     */
    public function store():Response
    {
        $couponCode = $this->request->get('couponCode', '');

        /**
         * @var CouponService $couponService
         */
        $couponService = pluginApp(CouponService::class);
        try {
            $response = $couponService->setCoupon($couponCode);
            return $this->response->create( $response, ResponseCode::CREATED );
        } catch (\Exception $e) {
            return $this->response->create( null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove a coupon from the basket.
     * @param string $selector Not used.
     * @return Response
     */
    public function destroy(string $selector):Response
    {
        /**
         * @var CouponService $couponService
         */
        $couponService = pluginApp(CouponService::class);
        $response = $couponService->removeCoupon();

        return $this->response->create( $response, ResponseCode::CREATED );
    }
}
