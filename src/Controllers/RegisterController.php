<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class RegisterController
 * @package IO\Controllers
 */
class RegisterController extends LayoutController
{
    /**
     * Prepare and render the data for the registration
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
