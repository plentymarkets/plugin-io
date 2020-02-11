<?php //strict

namespace IO\Controllers;

use IO\Helper\RouteConfig;
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

    public function redirect()
    {
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::CONTACT);
    }
}
