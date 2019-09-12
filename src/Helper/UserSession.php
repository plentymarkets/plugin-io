<?php //strict

namespace IO\Helper;

use Plenty\Modules\Frontend\Services\AccountService;

/**
 * Class UserSession
 * @package IO\Helper
 *
 * @deprecated since 4.3.0
 * Use AccountService instead
 */
class UserSession
{
	/**
	 * @var AccountService
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
     * Get the current contact ID
     * @return int
     *
     * @deprecated since 4.3.0
     * Use AccountService::getAccountContactId instead
     */
	public function getCurrentContactId():int
	{
		return $this->accountService->getAccountContactId();
	}

    /**
     * Check whether contact is logged in
     * @return bool
     *
     * @deprecated since 4.3.0
     * Use AccountService::getIsAccountLoggedIn instead
     */
	public function isContactLoggedIn():bool
	{
		return $this->accountService->getIsAccountLoggedIn();
	}
}
