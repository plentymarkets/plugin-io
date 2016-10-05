<?php //strict

namespace LayoutCore\Helper;

use Plenty\Modules\Frontend\Services\AccountService;

/**
 * Class UserSession
 * @package LayoutCore\Helper
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
     * get current contact id
     * @return int
     */
	public function getCurrentContactId():int
	{
		return $this->accountService->getAccountContactId();
	}
    
    /**
     * check if contact is logged in
     * @return bool
     */
	public function isContactLoggedIn():bool
	{
		return $this->accountService->getIsAccountLoggedIn();
	}
}
