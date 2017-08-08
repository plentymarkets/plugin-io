<?php //strict

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use IO\Services\BasketService;
use IO\Services\SessionStorageService;

/**
 * Class AuthenticationService
 * @package IO\Services
 */
class AuthenticationService
{
	/**
	 * @var ContactAuthenticationRepositoryContract
	 */
	private $contactAuthRepository;
    
    /**
     * @var SessionStorageService $sessionStorage
     */
	private $sessionStorage;

    /**
     * AuthenticationService constructor.
     * @param ContactAuthenticationRepositoryContract $contactAuthRepository
     */
	public function __construct(ContactAuthenticationRepositoryContract $contactAuthRepository, SessionStorageService $sessionStorage)
	{
		$this->contactAuthRepository = $contactAuthRepository;
		$this->sessionStorage = $sessionStorage;
	}

    /**
     * Perform the login with email and password
     * @param string $email
     * @param string $password
     */
	public function login(string $email, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactEmail($email, $password);
		$this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST_MIGRATION, true);
	}

    /**
     * Perform the login with customer ID and password
     * @param int $contactId
     * @param string $password
     */
	public function loginWithContactId(int $contactId, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactId($contactId, $password);
        $this->sessionStorage->setSessionValue(SessionStorageKeys::GUEST_WISHLIST_MIGRATION, true);
	}

    /**
     * Log out the customer
     */
	public function logout()
	{
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        $basketService->setBillingAddressId(0);
        $basketService->setDeliveryAddressId(0);
        
		$this->contactAuthRepository->logout();
	}
}
