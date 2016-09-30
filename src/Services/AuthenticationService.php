<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;

/**
 * Class AuthenticationService
 * @package LayoutCore\Services
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
     * perform login with email and password
     * @param string $email
     * @param string $password
     */
	public function login(string $email, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactEmail($email, $password);
	}
    
    /**
     * perform login with customer id and password
     * @param int $contactId
     * @param string $password
     */
	public function loginWithContactId(int $contactId, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactId($contactId, $password);
	}
    
    /**
     * log out the customer
     */
	public function logout()
	{
		$this->contactAuthRepository->logout();
	}
}
