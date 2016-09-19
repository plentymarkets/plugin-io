<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

class ConfirmationController extends LayoutController
{
	public function showConfirmation(): string
	{
		return $this->renderTemplate(
			"tpl.confirmation",
			[
				"confirmation" => ""
			]
		);
	}
}
