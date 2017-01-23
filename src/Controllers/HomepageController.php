<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

/**
 * Class HomepageController
 * @package IO\Controllers
 */
class HomepageController extends LayoutController
{
    /**
     * Prepare and render the data for the homepage
     * @return string
     */
    public function showHomepage():string
    {
        return $this->renderTemplate(
            "tpl.home",
            [
                "object" => ""
            ]
        );
    }
}
