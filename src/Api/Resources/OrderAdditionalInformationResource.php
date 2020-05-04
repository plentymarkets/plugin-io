<?php //strict

namespace IO\Api\Resources;

use IO\Services\SessionStorageService;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

/**
 * Class OrderAdditionalInformationResource
 * @package IO\Api\Resources
 */
class OrderAdditionalInformationResource extends ApiResource
{

    private $sessionStorageRepository;

    public function __construct(
        Request $request,
        ApiResponse $response,
        SessionStorageRepositoryContract $sessionStorageRepository
    ) {
        parent::__construct($request, $response);
        $this->sessionStorageRepository = $sessionStorageRepository;
    }

    public function store(): Response
    {
        $this->setContactWish();
        $this->setCustomerSign();
        $this->setShippingPrivacyHint();
        $this->setNewsletterSubscriptions();

        return $this->response->create('', ResponseCode::CREATED);
    }

    private function setContactWish()
    {
        $orderContactWish = $this->request->get('orderContactWish', '');

        if (strlen($orderContactWish)) {
            $this->sessionStorageRepository->setSessionValue(
                SessionStorageRepositoryContract::ORDER_CONTACT_WISH,
                $orderContactWish
            );
        }
    }

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

    private function setShippingPrivacyHint()
    {
        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::SHIPPING_PRIVACY_HINT_ACCEPTED,
            $this->request->get('shippingPrivacyHintAccepted', 'false')
        );
    }

    private function setNewsletterSubscriptions()
    {
        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::NEWSLETTER_SUBSCRIPTIONS,
            $this->request->get('newsletterSubscriptions', [])
        );
    }
}
