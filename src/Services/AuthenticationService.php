<?php //strict

namespace IO\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract as CoreContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class AuthenticationService
 *
 * This service class contains various methods for authenticating customers and related tasks.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class AuthenticationService
{
    use Loggable;

    /**
     * @var ContactAuthenticationRepositoryContract The repository used for authenticating contacts
     */
    private $contactAuthRepository;

    /**
     * @var SessionStorageRepositoryContract $sessionStorageRepository The repository used for storing data in the session
     */
    private $sessionStorageRepository;

    /**
     * @var CustomerService This service is used for various tasks relating to customers
     */
    private $customerService;

    /**
     * AuthenticationService constructor.
     *
     * @param ContactAuthenticationRepositoryContract $contactAuthRepository The repository used for authenticating contacts
     * @param SessionStorageRepositoryContract $sessionStorageRepository The repository used for storing data in the session
     * @param CustomerService $customerService This service is used for various tasks relating to customers
     */
    public function __construct(
        ContactAuthenticationRepositoryContract $contactAuthRepository,
        SessionStorageRepositoryContract $sessionStorageRepository,
        CustomerService $customerService
    )
    {
        $this->contactAuthRepository = $contactAuthRepository;
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->customerService = $customerService;
    }

    /**
     * Perform the login with email and password
     *
     * @param string $email Contains the customers email address
     * @param string $password Contains the password used for this login attempt
     * @return int|null
     */
    public function login(string $email, string $password)
    {
        $this->customerService->deleteGuestAddresses();
        $this->customerService->resetGuestAddresses();

        $this->contactAuthRepository->authenticateWithContactEmail($email, $password);
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::GUEST_WISHLIST_MIGRATION, true);

        /** @var CoreContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(CoreContactRepositoryContract::class);

        return $contactRepository->getContactIdByEmail($email);
    }

    /**
     * Perform the login with customer ID and password
     *
     * @param int $contactId Contains a id linked to a specific customer
     * @param string $password Contains the password used for this login attempt
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
     * Check if a password is valid. The customer is logged in as a side effect, if password is valid
     *
     * @param string $password Contains the password
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
     * Check if the current user is logged in.
     *
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

    /**
     * Perform the login with email and password and logout other devices
     *
     * @param string $email Contains the customers email address
     * @param string $password Contains the password used for this login attempt
     */
    public function loginAndLogoutOtherDevices(string $email, string $password)
    {
        $result = $this->login($email, $password);
        $this->contactAuthRepository->logoutOtherDevices($password);
    }
}