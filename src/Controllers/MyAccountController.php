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
     * prepare and render data for my account
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
