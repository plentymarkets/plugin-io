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
     * prepare and render data for guest registration
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
