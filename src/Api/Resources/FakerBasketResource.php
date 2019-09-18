<?php //strict

namespace IO\Api\Resources;

use IO\Services\Basket\Factories\BasketResultFactory;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class FakerBasketResource
 * @package IO\Api\Resources
 */
class FakerBasketResource extends ApiResource
{
    /**
     * @var BasketResultFactory
     */
    private $basketResultFactory;

    /**
     * FakerBasketResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param BasketResultFactory $basketResultFactory
     */
    public function __construct(Request $request, ApiResponse $response, BasketResultFactory $basketResultFactory)
    {
        parent::__construct($request, $response);
        $this->basketResultFactory = $basketResultFactory;
    }

    /**
     * Get the faked basket
     * @return Response
     */
    public function index(): Response
    {
        $basket = $this->basketResultFactory->fillBasketResult();

        $basket = $basket;

        return $this->response->create($basket, ResponseCode::OK);
    }
}
