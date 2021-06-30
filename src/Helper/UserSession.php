<?php //strict

namespace IO\Helper;

use Plenty\Modules\Frontend\Services\AccountService;

/**
 * Class UserSession
 *
 * Helper class for the user session.
 *
 * @package IO\Helper
 * @deprecated since 4.3.0.
 * @see \Plenty\Modules\Frontend\Services\AccountService
 */
class UserSession
{
	/**
	 * @var AccountService $accountService The accountService backing the functionality.
	 */
	private $accountService;

    /**
     * UserSession constructor.
     * @param AccountService $accountService
     */
	public function __construct(AccountService $accountService)
	{
		$this->accountService = $accountService;
	}

    /**
     * Get the current contact ID.
     * @return int
     *
     * @deprecated since 4.3.0.
     * @see AccountService::getAccountContactId
     */
	public function getCurrentContactId():int
	{
		return $this->accountService->getAccountContactId();
	}

    /**
     * Check whether contact is logged in.
     * @return bool
     *
     * @deprecated since 4.3.0
     * @see AccountService::getIsAccountLoggedIn
     */
	public function isContactLoggedIn():bool
	{
		return $this->accountService->getIsAccountLoggedIn();
	}
}
