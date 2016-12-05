<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

/**
 * Class GuestController
 * @package LayoutCore\Controllers
 */
class GuestController extends LayoutController
{
    /**
     * Prepare and render the data for the guest registration
     * @return string
     */
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
