<?php

namespace IO\Api\Resources;

use IO\Services\BasketService;
use IO\Services\ItemLastSeenService;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ResponseCode;

/**
 * Class SessionResource
 *
 * Resource class for the route `io/session`.
 * Used to get all session based information at once
 * by combining data for basket, basket items and customer.
 *
 * @package IO\Api\Resources
 */
class SessionResource extends ApiResource
{
    /**
     * Get the basket.
     * @return Response
     */
    public function index(): Response
    {
        if(($variationId = (int)$this->request->get('lastSeenVariationId', 0)) > 0) {
            /** @var ItemLastSeenService $itemLastSeenService */
            $itemLastSeenService = pluginApp(ItemLastSeenService::class);
            $itemLastSeenService->setLastSeenItem($variationId);
        }

        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);
        $guestMail = $sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL);

        return $this->response->create(
            [
                'basket' => $this->indexBasket(),
                'basketItems' => $this->indexBasketItems(),
                'customer' => $this->indexCustomer(),
                'guest' => $guestMail
            ],
            ResponseCode::OK
        );
    }

    protected function indexBasket()
    {
        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);
        return $basketService->getBasketForTemplate();
    }

    protected function indexBasketItems()
    {
        /** @var BasketService $basketService */
        $basketService = pluginApp(BasketService::class);

        return $basketService->getBasketItemsForTemplate(
            $this->request->get('template', '')
        );
    }

    protected function indexCustomer()
    {
        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);
        return $contactRepository->getContact();
    }
}
