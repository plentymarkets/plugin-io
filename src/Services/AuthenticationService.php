<?php //strict

namespace IO\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;
use IO\Services\BasketService;

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
        /**
         * @var BasketService $basketService
         */
        $basketService = pluginApp(BasketService::class);
        $basketService->setBillingAddressId(0);
        $basketService->setDeliveryAddressId(0);
        
		$this->contactAuthRepository->logout();
	}
}
