<?php //strict
namespace IO\Controllers;

use IO\Helper\RouteConfig;

/**
 * Class HomepageController
 * @package IO\Controllers
 */
class StaticPagesController extends LayoutController
{
    /**
     * Prepare and render the data for the cancellation rights page
     * @return string
     */
    public function showCancellationRights():string
    {
        /*$shopBuilderCategoryId =  RouteConfig::getCategoryId(RouteConfig::CANCELLATION_RIGHTS);
        if($shopBuilderCategoryId > 0)
        {
            return $this->showShopBuilderCategory($shopBuilderCategoryId);
        }*/
        
        return $this->renderTemplate(
            "tpl.cancellation-rights",
            [
                "object" => ""
            ]
        );
    }

    /**
     * Prepare and render the data for the cancellation form page
     * @return string
     */
    public function showCancellationForm():string
    {
        return $this->renderTemplate(
            "tpl.cancellation-form",
            [
                "object" => ""
            ]
        );
    }

    /**
     * Prepare and render the data for the legal disclosure page
     * @return string
     */
    public function showLegalDisclosure():string
    {
        return $this->renderTemplate(
            "tpl.legal-disclosure",
            [
                "object" => ""
            ]
        );
    }

    /**
     * Prepare and render the data for the privacy policy page
     * @return string
     */
    public function showPrivacyPolicy():string
    {
        return $this->renderTemplate(
            "tpl.privacy-policy",
            [
                "object" => ""
            ]
        );
    }

    /**
     * Prepare and render the data for the terms and conditions page
     * @return string
     */
    public function showTermsAndConditions():string
    {
        return $this->renderTemplate(
            "tpl.terms-conditions",
            [
                "object" => ""
            ]
        );
    }

    /**
     * Prepare and render the data for the page not found page
     * @return string
     */
    public function showPageNotFound():string
    {
        return $this->renderTemplate(
            "tpl.page-not-found",
            [
                "object" => ""
            ],
            false
        );
    }
    
    /*private function getShopBuilderCategory($type)
    {
        return
    }*/
    
    private function showShopBuilderCategory($categoryId)
    {
        if($categoryId > 0)
        {
            
            /** @var CategoryController $categoryController */
            $categoryController = pluginApp(CategoryController::class);
    
            return $categoryController->showCategoryById($categoryId);
            
            //$categoryController->showCategoryById($categoryId);
            //return $categoryController->redirectToCategory($categoryUrl);
        }
    }
}
