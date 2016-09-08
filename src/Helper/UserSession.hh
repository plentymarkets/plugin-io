<?hh //strict

namespace LayoutCore\Helper;

use Plenty\Modules\Frontend\Services\AccountService;

class UserSession
{
    private AccountService $accountService;

    public function __construct( AccountService $accountService )
    {
        $this->accountService = $accountService;
    }

    public function getCurrentContactId():int
    {
        return $this->accountService->getAccountContactId();
    }

    public function isContactLoggedIn():bool
    {
        return $this->accountService->getIsAccountLoggedIn();
    }
}
