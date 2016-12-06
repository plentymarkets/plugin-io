<?php //strict
namespace IO\Controllers;

use IO\Guards\AuthGuard;

/**
 * Class MyAccountController
 * @package IO\Controllers
 */
class MyAccountController extends LayoutController
{
    /**
     * Prepare and render the data for the my account page
     * @return string
     */
	public function showMyAccount( AuthGuard $guard ): string
	{
        $guard->assertOrRedirect( true, "/login" );

		return $this->renderTemplate( "tpl.my-account" );
	}
}
