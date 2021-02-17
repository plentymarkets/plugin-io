<?php

namespace IO\Api\Resources;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class OrderAdditionalInformationResource
 *
 * Resource class for the route `io/order/additional_information`.
 * @package IO\Api\Resources
 */
class OrderAdditionalInformationResource extends ApiResource
{
    /**
     * @var SessionStorageRepositoryContract $sessionStorageRepository The instance of the SessionStorageRepository.
     */
    private $sessionStorageRepository;

    /**
     * OrderAdditionalInformationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        SessionStorageRepositoryContract $sessionStorageRepository
    ) {
        parent::__construct($request, $response);
        $this->sessionStorageRepository = $sessionStorageRepository;
    }

    /**
     * Store additional information about the order in the current session.
     * - contact wish
     * - customer sign
     * - shipping privacy hint
     * - newsletter subscriptions
     * @return Response
     */
    public function store(): Response
    {
        $this->setContactWish();
        $this->setCustomerSign();
        $this->setShippingPrivacyHint();
        $this->setNewsletterSubscriptions();

        return $this->response->create('', ResponseCode::CREATED);
    }

    /**
     * Set the contact wish for the current session taken from the request.
     */
    private function setContactWish()
    {
        $orderContactWish = $this->request->get('orderContactWish', '');

        if (!strlen($orderContactWish)) {
            $orderContactWish = null;
        }
        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::ORDER_CONTACT_WISH,
            $orderContactWish
        );
    }

    /**
     * Set the customer sign for the current session taken from the request.
     */
    private function setCustomerSign()
    {
        $orderCustomerSign = $this->request->get('orderCustomerSign', '');

        if (strlen($orderCustomerSign)) {
            $this->sessionStorageRepository->setSessionValue(
                SessionStorageRepositoryContract::ORDER_CUSTOMER_SIGN,
                $orderCustomerSign
            );
        }
    }

    /**
     * Set the shipping privacy hint for the current session taken from the request.
     */
    private function setShippingPrivacyHint()
    {
        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::SHIPPING_PRIVACY_HINT_ACCEPTED,
            $this->request->get('shippingPrivacyHintAccepted', 'false')
        );
    }

    /**
     * Set the newsletter subscriptions for the current session taken from the request.
     */
    private function setNewsletterSubscriptions()
    {
        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::NEWSLETTER_SUBSCRIPTIONS,
            $this->request->get('newsletterSubscriptions', [])
        );
    }
}
