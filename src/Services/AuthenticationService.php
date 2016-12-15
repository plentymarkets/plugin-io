<?php //strict

namespace IO\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;

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
     * AuthenticationService constructor.
     * @param ContactAuthenticationRepositoryContract $contactAuthRepository
     */
	public function __construct(ContactAuthenticationRepositoryContract $contactAuthRepository)
	{
		$this->contactAuthRepository = $contactAuthRepository;
	}

    /**
     * Perform the login with email and password
     * @param string $email
     * @param string $password
     */
	public function login(string $email, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactEmail($email, $password);
	}

    /**
     * Perform the login with customer ID and password
     * @param int $contactId
     * @param string $password
     */
	public function loginWithContactId(int $contactId, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactId($contactId, $password);
	}

    /**
     * Log out the customer
     */
	public function logout()
	{
        $sessionStorage = pluginApp(SessionStorageService::class);
        $sessionStorage->setSessionValue(SessionStorageKeys::BILLING_ADDRESS_ID, 0);
        $sessionStorage->setSessionValue(SessionStorageKeys::DELIVERY_ADDRESS_ID, 0);
        
		$this->contactAuthRepository->logout();
	}
}
