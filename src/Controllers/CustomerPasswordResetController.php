<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class CustomerPasswordResetController
 * @package IO\Controllers
 */
class CustomerPasswordResetController extends LayoutController
{
    /**
     * Prepare and render the data for the guest registration
     * @return string
     */
    public function showReset($contactId, $hash): string
    {
        return $this->renderTemplate(
            "tpl.password-reset",
            [
                "contactId" => $contactId,
                "hash"      => $hash
            ]
        );
    }
}
