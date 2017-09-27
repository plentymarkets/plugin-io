<?php //strict
namespace IO\Controllers;

use Plenty\Plugin\Http\Response;
use IO\Guards\AuthGuard;
use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;

/**
 * Class RegisterController
 * @package IO\Controllers
 */
class RegisterController extends LayoutController
{
    /**
     * Prepare and render the data for the registration
     * @param CustomerService $customerService
     * @return string
     */
	public function showRegister(CustomerService $customerService): string
	{
	    if($customerService->getContactId() > 0)
        {
            AuthGuard::redirect("/", []);
        }
	
		return $this->renderTemplate(
			"tpl.register",
			[
				"register" => ""
			]
		);
	}
	
	public function redirectRegister()
    {
        return pluginApp(Response::class)->redirectTo('register');
    }
}
