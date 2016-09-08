<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Authentication\Contracts\ContactAuthenticationRepositoryContract;

class AuthenticationService
{
    private ContactAuthenticationRepositoryContract $contactAuthRepository;

    public function __construct(ContactAuthenticationRepositoryContract $contactAuthRepository)
    {
        $this->contactAuthRepository = $contactAuthRepository;
    }

    public function login(string $email, string $password):void
    {
        $this->contactAuthRepository->authenticateWithContactEmail($email, $password);
    }

    public function loginWithContactId(int $contactId, string $password):void
    {
        $this->contactAuthRepository->authenticateWithContactId($contactId, $password);
    }

    public function logout():void
    {
        $this->contactAuthRepository->logout();
    }
}
