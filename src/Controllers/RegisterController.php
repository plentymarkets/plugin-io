<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

/**
 * Class RegisterController
 * @package LayoutCore\Controllers
 */
class RegisterController extends LayoutController
{
    /**
     * prepare and render data for registration
     * @return string
     */
	public function showRegister(): string
	{
		return $this->renderTemplate(
			"tpl.register",
			[
				"register" => ""
			]
		);
	}
}
