<?php //strict

namespace IO\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;
use IO\Services\BasketService;
use IO\DBModels\PasswordReset;
use IO\Services\CustomerPasswordResetService;

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
     * @var CustomerPasswordResetService $customerPasswordResetService
     */
	private $customerPasswordResetService;
    
    /**
     * AuthenticationService constructor.
     * @param ContactAuthenticationRepositoryContract $contactAuthRepository
     * @param \IO\Services\SessionStorageService $sessionStorage
     * @param \IO\Services\CustomerPasswordResetService $customerPasswordResetService
     */
	public function __construct(ContactAuthenticationRepositoryContract $contactAuthRepository, SessionStorageService $sessionStorage, CustomerPasswordResetService $customerPasswordResetService)
	{
		$this->contactAuthRepository = $contactAuthRepository;
		$this->sessionStorage = $sessionStorage;
		$this->customerPasswordResetService = $customerPasswordResetService;
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
		
		$contactId = $this->customerPasswordResetService->getContactIdbyEmailAddress($email);
        $this->checkPasswordResetExpiration($contactId);
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
        $this->checkPasswordResetExpiration($contactId);
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
	
	private function checkPasswordResetExpiration($contactId)
    {
        if((int)$contactId > 0)
        {
            $existingPasswordResetEntry = $this->customerPasswordResetService->findExistingHash($contactId);
            if($existingPasswordResetEntry instanceof PasswordReset)
            {
                if(!$this->customerPasswordResetService->checkHashExpiration($existingPasswordResetEntry->timestamp))
                {
                    $this->customerPasswordResetService->deleteHash($contactId);
                }
            }
        }
    }
}
