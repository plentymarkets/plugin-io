<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

class GuestController extends LayoutController
{
	public function showGuest(): string
	{
		return $this->renderTemplate(
			"tpl.guest",
			[
				"guest" => ""
			]
		);
	}
}
