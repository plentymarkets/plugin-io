<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

/**
 * Class LoginController
 * @package LayoutCore\Controllers
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
