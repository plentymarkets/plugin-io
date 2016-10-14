<?php //strict
namespace LayoutCore\Controllers;

use LayoutCore\Guards\AuthGuard;
use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Helper\UserSession;
use LayoutCore\Helper\RouteGuard;

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
        AuthGuard::assertOrRedirect( true, "/login" );

		return $this->renderTemplate(
			"tpl.my-account",
			[
				"my-account" => ""
			]
		);
	}
}
