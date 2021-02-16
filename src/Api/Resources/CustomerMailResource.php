<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Builder\Order\AddressType;
use IO\DBModels\UserDataHash;
use IO\Extensions\Constants\ShopUrls;
use IO\Extensions\Mail\SendMail;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use IO\Services\AuthenticationService;
use IO\Services\CustomerService;
use IO\Services\UserDataHashService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract as CoreContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailContact;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CustomerMailResource
 *
 * Resource class for the route `io/customer/mail`.
 * @package IO\Api\Resources
 */
class CustomerMailResource extends ApiResource
{
    use Loggable;
    use SendMail;

    /**
     * CustomerMailResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Trigger the creation of an email with instructions to change the email.
     * @return Response
     */
    public function store(): Response
    {
        $newMail = $this->request->get('newMail', null);
        $newMail2 = $this->request->get('newMail2', null);

        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        /** @var Contact $contact */
        $contact = $contactRepository->getContact();

        if (is_null($contact)) {
            return $this->response->create(null, ResponseCode::UNAUTHORIZED);
        }

        if (strlen($newMail) <= 0 || strlen($newMail2) <= 0 || $newMail !== $newMail2) {
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        if ($newMail === $contact->email) {
            return $this->response->create(null, ResponseCode::NOT_MODIFIED);
        }

        /** @var CoreContactRepositoryContract $coreContactRepository */
        $coreContactRepository = pluginApp(CoreContactRepositoryContract::class);
        $contactByMail = $coreContactRepository->getContactIdByEmail($newMail);

        if (!is_null($contactByMail)) {
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $userDataHash = $hashService->create(
            ['newMail' => $newMail],
            UserDataHash::TYPE_CHANGE_MAIL
        );

        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);


        $clientId = Utils::getPlentyId();

        /**  @var WebstoreConfiguration $webstoreConfiguration */
        $webstoreConfiguration = $webstoreConfigurationRepository->getWebstoreConfiguration();

        /** @var string $domain */
        $domain = $webstoreConfiguration->domainSsl;
        $defaultLang = $webstoreConfiguration->defaultLanguage;

        $lang = Utils::getLang();
        /** @var ShopUrls $shopUrls */
        $shopUrls = pluginApp(ShopUrls::class);
        $newEmailLink = "";

        if (RouteConfig::getCategoryId(RouteConfig::CHANGE_MAIL) <= 0) {
            $newEmailLink = $domain . ($lang != $defaultLang ? '/' . $lang : '') . '/change-mail/' . $userDataHash->contactId . '/' . $userDataHash->hash;
        } else {
            $newEmailLink = $domain . $shopUrls->changeMail . '?contactId=' . $userDataHash->contactId . '&hash=' . $userDataHash->hash;
        }

        $params = [
            'contactId' => $contact->id,
            'clientId' => Utils::getWebstoreId(),
            'password' => null,
            'newEmailLink' => $newEmailLink,
            'language' => Utils::getLang()
        ];

        $this->sendMail(AutomaticEmailTemplate::CONTACT_NEW_EMAIL, AutomaticEmailContact::class, $params);

        return $this->response->create(null, ResponseCode::OK);
    }

    /**
     * Update the email of a contact.
     * @param string $contactId The id of the contact.
     * @return Response
     */
    public function update(string $contactId): Response
    {
        $password = $this->request->get('password');
        $hash = $this->request->get('hash');

        if (!strlen($password)) {
            return $this->response->create(null, ResponseCode::UNAUTHORIZED);
        }

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData($hash, $contactId);

        if (is_null($hashData)) {
            return $this->response->create(null, ResponseCode::NOT_FOUND);
        }

        try {
            /** @var AuthenticationService $authService */
            $authService = pluginApp(AuthenticationService::class);
            $authService->loginWithContactId((int)$contactId, (string)$password);
        } catch (\Exception $exception) {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.CustomerMailResource_loginFailed",
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                    "trace" => $exception->getTraceAsString()
                ]
            );
            return $this->response->create(null, ResponseCode::UNAUTHORIZED);
        }

        $contact = null;
        if ($contactId > 0) {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $contact = $authHelper->processUnguarded(
                function () use ($contactId, $hashData) {
                    /** @var CoreContactRepositoryContract $coreContactRepository */
                    $coreContactRepository = pluginApp(CoreContactRepositoryContract::class);
                    $result = $coreContactRepository->updateContact(
                        [
                            'options' => [
                                [
                                    'typeId' => ContactOption::TYPE_MAIL,
                                    'subTypeId' => ContactOption::SUBTYPE_PRIVATE,
                                    'value' => $hashData['newMail'],
                                    'priority' => 0
                                ]
                            ]
                        ],
                        $contactId
                    );

                    return $result;
                }
            );

            //update all addresses of the contact with the new email

            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);

            $billingAddressList = $customerService->getAddresses(AddressType::BILLING);
            foreach ($billingAddressList as $billingAddress) {
                $customerService->updateAddress($billingAddress->id, $billingAddress->toArray(), AddressType::BILLING);
            }

            $deliveryAddressList = $customerService->getAddresses(AddressType::DELIVERY);
            foreach ($deliveryAddressList as $deliveryAddress) {
                $customerService->updateAddress($deliveryAddress->id, $deliveryAddress->toArray(), AddressType::DELIVERY);
            }

            $hashService->delete($hash);
        }
        return $this->response->create($contact, ResponseCode::OK);
    }
}
