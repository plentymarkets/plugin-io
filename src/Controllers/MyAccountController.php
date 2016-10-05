<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

/**
 * Class MyAccountController
 * @package LayoutCore\Controllers
 */
class MyAccountController extends LayoutController
{
    /**
     * Prepare and render the data for the my account page
     * @return string
     */
	public function showMyAccount(): string
	{
		return $this->renderTemplate(
			"tpl.my-account",
			[
				"my-account" => ""
			]
		);
	}
}
