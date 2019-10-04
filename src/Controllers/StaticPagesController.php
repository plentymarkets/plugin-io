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
     * @throws \ErrorException
     */
    public function showCancellationRights():string
    {
        return $this->renderTemplate(
            "tpl.cancellation-rights",
            [
                "object" => ""
            ]
        );
    }

    /**
     * @return mixed
     */
    public function redirectCancellationRights()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::CANCELLATION_RIGHTS);
    }

    /**
     * Prepare and render the data for the cancellation form page
     * @return string
     * @throws \ErrorException
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
     * @return mixed
     */
    public function redirectCancellationForm()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::CANCELLATION_FORM);
    }

    /**
     * Prepare and render the data for the legal disclosure page
     * @return string
     * @throws \ErrorException
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
     * @return mixed
     */
    public function redirectLegalDisclosure()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::LEGAL_DISCLOSURE);
    }

    /**
     * Prepare and render the data for the privacy policy page
     * @return string
     * @throws \ErrorException
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
     * @return mixed
     */
    public function redirectPrivacyPolicy()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::PRIVACY_POLICY);
    }

    /**
     * Prepare and render the data for the terms and conditions page
     * @return string
     * @throws \ErrorException
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
     * @return mixed
     */
    public function redirectTermsAndConditions()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::TERMS_CONDITIONS);
    }

    /**
     * Prepare and render the data for the page not found page
     * @return string
     * @throws \ErrorException
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

    /**
     * @return mixed
     */
    public function redirectPageNotFound()
    {
        return pluginApp(CategoryController::class)->redirectRoute(RouteConfig::PAGE_NOT_FOUND);
    }
}
