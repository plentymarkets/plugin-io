<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class GuestController
 * @package IO\Controllers
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
