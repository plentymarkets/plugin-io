<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class ConfirmationController
 * @package IO\Controllers
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
