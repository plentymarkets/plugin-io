<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

class MyAccountController extends LayoutController
{
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
