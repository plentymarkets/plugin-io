<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;

/**
 * Class ConfirmationController
 * @package LayoutCore\Controllers
 */
class ConfirmationController extends LayoutController
{
    /**
     * Prepare and render the data for the order confirmation
     * @return string
     */
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
