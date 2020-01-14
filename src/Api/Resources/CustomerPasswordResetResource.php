<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\DBModels\UserDataHash;
use IO\Extensions\Mail\SendMail;
use IO\Helper\Utils;
use IO\Services\UserDataHashService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailContact;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmailTemplate;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class CustomerPasswordResetResource
 * @package IO\Api\Resources
 */
class CustomerPasswordResetResource extends ApiResource
{
    use SendMail;

    /**
     * CustomerPasswordResetResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Set the password for the contact
     * @return Response
     */
    public function store():Response
    {
        $email = $this->request->get('email', '');


        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $contact = $authHelper->processUnguarded(function() use ($email)
        {
            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);

            $contactId = $contactRepository->getContactIdByEmail($email);

            if ( $contactId > 0 )
            {
                return $contactRepository->findContactById($contactId);
            }

            return null;
        });

        if ($contact instanceof Contact && $contact->id > 0)
        {
            /** @var UserDataHashService $hashService */
            $hashService = pluginApp(UserDataHashService::class);
            $hashService->create(['mail' => $email], UserDataHash::TYPE_RESET_PASSWORD, null, $contact->id);

            $params = ['contactId' => $contact->id];
            $this->sendMail(AutomaticEmailTemplate::CONTACT_NEW_PASSWORD, AutomaticEmailContact::class, $params);

            return $this->response->create(true, ResponseCode::OK);
        }

        return $this->response->create(null, ResponseCode::BAD_REQUEST);

    }
    
}


