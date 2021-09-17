<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use IO\Api\ResponseCode;

/**
 * Class BasketResource
 *
 * Resource class for the route `io/basket`.
 * @package IO\Api\Resources
 */
class BasketResource extends SessionResource
{
    /**
     * Get the basket.
     * @return Response
     */
    public function index(): Response
    {
		return $this->response->create($this->indexBasket(), ResponseCode::OK);
	}
}
