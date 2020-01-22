<?php //strict

namespace IO\Services;

use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class AuthenticationService
 * @package IO\Services
 */
class AuthenticationService
{
    use Loggable;

    /**
     * @var ContactAuthenticationRepositoryContract
     */
    private $contactAuthRepository;

    /**
     * @var SessionStorageRepositoryContract $sessionStorageRepository
     */
    private $sessionStorageRepository;

    /** @var CustomerService */
    private $customerService;

    /**
     * AuthenticationService constructor.
     * @param ContactAuthenticationRepositoryContract $contactAuthRepository
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     */
    public function __construct(
        ContactAuthenticationRepositoryContract $contactAuthRepository,
        SessionStorageRepositoryContract $sessionStorageRepository,
        CustomerService $customerService
    ) {
        $this->contactAuthRepository = $contactAuthRepository;
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->customerService = $customerService;
    }

    /**
     * Perform the login with email and password
     * @param string $email
     * @param string $password
     * @return int|null
     */
    public function login(string $email, string $password)
    {
        $this->customerService->deleteGuestAddresses();
        $this->customerService->resetGuestAddresses();

        $this->contactAuthRepository->authenticateWithContactEmail($email, $password);
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION, true);

        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        return $contactRepository->getContactIdByEmail($email);
    }

    /**
     * Perform the login with customer ID and password
     * @param int $contactId
     * @param string $password
     */
    public function loginWithContactId(int $contactId, string $password): void
    {
        $this->contactAuthRepository->authenticateWithContactId($contactId, $password);
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION, true);
    }

    /**
     * Log out the customer
     */
    public function logout(): void
    {
        $this->contactAuthRepository->logout();

        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        $basketService->setBillingAddressId(0);
        $basketService->setDeliveryAddressId(0);
    }

    /**
     * @param string $password
     * @return bool
     */
    public function checkPassword($password): bool
    {
        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        $contact = $contactRepository->getContact();
        if ($contact instanceof Contact) {
            try {
                $this->login(
                    $contact->email,
                    $password
                );
                return true;
            } catch (\Exception $e) {
                $this->getLogger(__CLASS__)->info(
                    'IO::Debug.AuthenticationService_invalidPassword',
                    [
                        'contactId' => $contact->id
                    ]
                );
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        $contactId = $contactRepository->getContactId();
        $email = $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::GUEST_EMAIL);

        return $contactId > 0 || !empty(trim($email));
    }
}
