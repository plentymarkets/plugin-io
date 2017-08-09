<?php //strict

namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class ContactController
 * @package IO\Controllers
 */
class ContactController extends LayoutController
{
    /**
     * Prepare and render the data for the contact page
     * @return string
     */
    public function showContact():string
    {
        return $this->renderTemplate(
            "tpl.contact",
            [
                "object" => ""
            ]
        );
    }
}
