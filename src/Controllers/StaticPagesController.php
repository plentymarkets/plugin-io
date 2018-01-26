<?php //strict

namespace IO\Controllers;

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
        return $this->renderTemplate(
            "tpl.cancellation-rights",
            [
                "metaDescriptionKey" => "description_cancellation_rights",
                "metaRobotsKey"      => "robots_cancellation_rights",
                "pageTitle"          => "generalCancellationRightNoShy",
                "contentTemplate"    => "Ceres::StaticPages.StaticPagesContent.CancellationRightsContent"
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
                "metaDescriptionKey" => "description_cancellation_form",
                "metaRobotsKey"      => "robots_cancellation_form",
                "pageTitle"          => "generalCancellationFormNoShy",
                "contentTemplate"    => "Ceres::StaticPages.StaticPagesContent.CancellationFormContent"
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
                "metaDescriptionKey" => "description_legal_disclosure",
                "metaRobotsKey"      => "robots_legal_disclosure",
                "pageTitle"          => "generalLegalDisclosure",
                "contentTemplate"    => "Ceres::StaticPages.StaticPagesContent.LegalDisclosureContent"
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
                "metaDescriptionKey" => "description_privacy_policy",
                "metaRobotsKey"      => "robots_privacy_policy",
                "pageTitle"          => "generalPrivacyPolicyNoShy",
                "contentTemplate"    => "Ceres::StaticPages.StaticPagesContent.PrivacyPolicyContent"
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
                "metaDescriptionKey" => "description_terms_and_conditions",
                "metaRobotsKey"      => "robots_terms_and_conditions",
                "pageTitle"          => "generalGtc",
                "contentTemplate"    => "Ceres::StaticPages.StaticPagesContent.TermsAndConditionsContent"
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
