<?php

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\DBModels\UserDataHash;
use IO\Extensions\Mail\SendMail;
use IO\Services\AuthenticationService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Services\UserDataHashService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailContact;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

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

    public function store():Response
    {
        $oldMail  = $this->request->get('oldMail', null);
        $newMail  = $this->request->get('newMail', null);
        $newMail2 = $this->request->get('newMail2', null);

        /** @var Contact $contact */
        $contact  = pluginApp(CustomerService::class)->getContact();

        if ( is_null($contact) )
        {
            return $this->response->create(null, ResponseCode::UNAUTHORIZED );
        }

        if ( $oldMail !== $contact->email
            || strlen($newMail) <= 0
            || strlen($newMail2) <= 0
            || $newMail !== $newMail2 )
        {
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $emailExists = $authHelper->processUnguarded(function() use ($contact, $newMail)
        {
            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);
            $contactByMail = $contactRepository->getContactIdByEmail($newMail);

            return !is_null($contactByMail);
        });

        if ( $emailExists )
        {
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $userDataHash = $hashService->create(
            ['newMail' => $newMail, 'oldMail' => $oldMail],
            UserDataHash::TYPE_CHANGE_MAIL
        );

        /**
         * @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository
         */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);

        /**
         * @var WebstoreConfiguration $webstoreConfiguration
         */
        $webstoreConfiguration = $webstoreConfigurationRepository->findByPlentyId($contact->plentyId);

         /** @var string $domain */
         $domain = $webstoreConfiguration->domainSsl;
         $defaultLang = $webstoreConfiguration->defaultLanguage;
         $sessionService = pluginApp(SessionStorageService::class);
         $lang = $sessionService->getLang();

         $newEmailLink = $domain . ($lang != $defaultLang ? '/' . $lang : ''). '/change-mail/'. $userDataHash->contactId . '/' . $userDataHash->hash;
         $params = ['contactId' => $contact->id, 'clientId' => $webstoreConfiguration->webstoreId, 'password' => null, 'newEmailLink' => $newEmailLink];

         $this->sendMail(AutomaticEmailTemplate::CONTACT_NEW_EMAIL , AutomaticEmailContact::class, $params);

         return $this->response->create($userDataHash->hash, ResponseCode::OK);
    }

    public function update(string $contactId):Response
    {
        $password  = $this->request->get('password');
        $hash      = $this->request->get('hash');

        if(!strlen($password))
        {
            return $this->response->create(null, ResponseCode::UNAUTHORIZED);
        }

        /** @var UserDataHashService $hashService */
        $hashService = pluginApp(UserDataHashService::class);
        $hashData = $hashService->getData( $hash, $contactId );

        if (is_null($hashData))
        {
            return $this->response->create(null, ResponseCode::NOT_FOUND);
        }

        try
        {
            /** @var AuthenticationService $authService */
            $authService = pluginApp(AuthenticationService::class);
            $authService->loginWithContactId($contactId, (string)$password);
        }
        catch(\Exception $exception)
        {
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
        if ( $contactId > 0 )
        {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $contact = $authHelper->processUnguarded(function() use ($contactId, $hashData)
            {
                /** @var ContactRepositoryContract $contactRepository */
                $contactRepository = pluginApp(ContactRepositoryContract::class);
                $result = $contactRepository->updateContact([
                    'options' => [
                        [
                            'typeId'    => ContactOption::TYPE_MAIL,
                            'subTypeId' => ContactOption::SUBTYPE_PRIVATE,
                            'value'     => $hashData['newMail'],
                            'priority'  => 0
                        ]
                    ]
                ], $contactId);

                return $result;
            });

            $hashService->delete( $hash );

        }
        return $this->response->create($contact, ResponseCode::OK);
    }
}
