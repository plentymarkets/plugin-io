<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;

class AuthenticationService
{
	/**
	 * @var ContactAuthenticationRepositoryContract
	 */
	private $contactAuthRepository;
	
	public function __construct(ContactAuthenticationRepositoryContract $contactAuthRepository)
	{
		$this->contactAuthRepository = $contactAuthRepository;
	}
	
	public function login(string $email, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactEmail($email, $password);
	}
	
	public function loginWithContactId(int $contactId, string $password)
	{
		$this->contactAuthRepository->authenticateWithContactId($contactId, $password);
	}
	
	public function logout()
	{
		$this->contactAuthRepository->logout();
	}
}
