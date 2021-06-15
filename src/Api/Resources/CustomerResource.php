<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Constants\LogLevel;
use IO\Helper\ReCaptcha;
use IO\Services\CustomerService;
use IO\Services\NotificationService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\Events\ValidateVatNumber;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class CustomerResource
 * @package IO\Api\Resources
 */
class CustomerResource extends ApiResource
{
    /** @var CustomerService */
    private $customerService;

    /** @var ContactRepositoryContract $contactRepository */
    private $contactRepository;

    /**
     * CustomerResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CustomerService $customerService
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        CustomerService $customerService,
        ContactRepositoryContract $contactRepository
    ) {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Get the contact
     * @return Response
     */
    public function index(): Response
    {
        $contact = $this->contactRepository->getContact();

        if (is_null($contact)) {
            /** @var SessionStorageRepositoryContract $sessionStorageRepository */
            $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

            $guestEmail = $sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL);

            if (strlen($guestEmail)) {
                $contact = [
                    'isGuest' => true,
                    'email' => $guestEmail
                ];
            }
        }

        return $this->response->create($contact, ResponseCode::OK);
    }

    /**
     * Save the contact
     * @return Response
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function store(): Response
    {
        // Honeypot check
        if (strlen($this->request->get('honeypot'))) {
            return $this->response->create(true, ResponseCode::OK);
        }

        if (!ReCaptcha::verify($this->request->get('recaptcha', null))) {
            /**
            * @var NotificationService $notificationService
            */
            $notificationService = pluginApp(NotificationService::class);
            $notificationService->addNotificationCode(LogLevel::ERROR, 13);

            return $this->response->create('', ResponseCode::BAD_REQUEST);
        }

        $contactData = $this->request->get('contact', null);
        $billingAddressData = $this->request->get('billingAddress', []);
        $deliveryAddressData = $this->request->get('deliveryAddress', []);

        if ($contactData === null || !is_array($contactData)) {
            $this->response->error(0, 'Missing contact data or unexpected format.');
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        if (!is_array($billingAddressData) || !is_array($deliveryAddressData)) {
            $this->response->error(0, 'Unexpected address format.');
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

         /** @var Dispatcher $eventDispatcher */
        $eventDispatcher = pluginApp(Dispatcher::class);

        if (count($billingAddressData) === 0) {
            $billingAddressData = null;
        } elseif (isset($billingAddressData['vatNumber']) && strlen($billingAddressData['vatNumber']) > 0) {
            /** @var ValidateVatNumber $val */
            $val = pluginApp(ValidateVatNumber::class, [$billingAddressData['vatNumber']]);
            $eventDispatcher->fire($val);
        }

        if (count($deliveryAddressData) === 0) {
            $deliveryAddressData = null;
        } elseif (isset($deliveryAddressData['vatNumber']) && strlen($deliveryAddressData['vatNumber']) > 0) {
            /** @var ValidateVatNumber $val */
            $val = pluginApp(ValidateVatNumber::class, [$deliveryAddressData['vatNumber']]);
            $eventDispatcher->fire($val);
        }

        $contact = $this->customerService->registerCustomer(
            $contactData,
            $billingAddressData,
            $deliveryAddressData
        );


        if (!$contact instanceof Contact) {
            $this->response->error(1, '');
            return $this->response->create($contact, ResponseCode::IM_USED);
        }

        return $this->index();
    }
}
