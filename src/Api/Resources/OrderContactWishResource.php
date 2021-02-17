<?php //strict

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class OrderContactWishResource
 *
 * Resource class for the route `io/order/contactWish`.
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

    /**
     * Set the order contact wish for the current session taken from the request.
     * @return Response
     */
    public function store(): Response
    {
        $orderContactWish = $this->request->get('orderContactWish', '');

        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        if (strlen($orderContactWish)) {
            $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::ORDER_CONTACT_WISH, $orderContactWish);
        }

        return $this->response->create($orderContactWish, ResponseCode::CREATED);
    }
}
