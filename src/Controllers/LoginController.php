<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

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
	public function showLogin(): string
	{
		return $this->renderTemplate(
			"tpl.login",
			[
				"login" => ""
			]
		);
	}
}
