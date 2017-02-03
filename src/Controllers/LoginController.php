<?php //strict
namespace IO\Controllers;

use IO\Guards\AuthGuard;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;

/**
 * Class LoginController
 * @package IO\Controllers
 */
class LoginController extends LayoutController
{
    /**
     * Prepare and render the data for the login
     * @return string
     */
	public function showLogin(CustomerService $customerService): string
	{
        if($customerService->getContactId() > 0)
       {
           AuthGuard::redirect("/", []);
       }

       return $this->renderTemplate(
           "tpl.login",
           [
               "login" => ""
           ]
       );
	}
}
