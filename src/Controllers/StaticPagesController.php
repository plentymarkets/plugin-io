<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;

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
        $fallbackContent = '{% extends getPartial(\'page-design\') %}';
        
        return $this->renderTemplate(
            "tpl.cancellation-rights",
            [
                "fallbackContent" => $fallbackContent
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
        $fallbackContent = '{% extends getPartial(\'page-design\') %}';
        $pageBodyStart = '{% block PageBody %}';
        $pageBodyEnd = '{% endblock %}';
        
        return $this->renderTemplate(
            "tpl.legal-disclosure",
            [
                "fallbackContent" => $fallbackContent,
                "pageBodyStart" => $pageBodyStart,
                "pageBodyEnd" => $pageBodyEnd
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
            ]
        );
    }
}
