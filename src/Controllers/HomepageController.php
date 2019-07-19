<?php //strict
namespace IO\Controllers;

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
    public function showHomepage()
    {
        return $this->renderTemplate(
            "tpl.home",
            [
                "object" => ""
            ]
        );
    }
}
