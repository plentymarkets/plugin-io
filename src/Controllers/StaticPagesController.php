<?php //strict

namespace IO\Controllers;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;

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
        return $this->renderFromConfig(
            "pages.cancellation_rights",
            "tpl.cancellation-rights",
            [
                "metaDescriptionKey" => "description_cancel_rights",
                "metaRobotsKey"      => "robots_cancel_rights",
                "pageTitle"          => "generalCancellationRightNoShy",
            ]
        );
    }

    /**
     * Prepare and render the data for the cancellation form page
     * @return string
     */
    public function showCancellationForm():string
    {
        return $this->renderFromConfig(
            "pages.cancellation_form",
            "tpl.cancellation-form",
            [
                "metaDescriptionKey" => "description_cancel_form",
                "metaRobotsKey"      => "robots_cancel_form",
                "pageTitle"          => "generalCancellationFormNoShy",
            ]
        );
    }

    /**
     * Prepare and render the data for the legal disclosure page
     * @return string
     */
    public function showLegalDisclosure():string
    {
        return $this->renderFromConfig(
            "pages.legal_disclosure",
            "tpl.legal-disclosure",
            [
                "metaDescriptionKey" => "description_legal_disclosure",
                "metaRobotsKey"      => "robots_legal_disclosure",
                "pageTitle"          => "generalLegalDisclosure",
            ]
        );
    }

    /**
     * Prepare and render the data for the privacy policy page
     * @return string
     */
    public function showPrivacyPolicy():string
    {
        return $this->renderFromConfig(
            "pages.privacy_policy",
            "tpl.privacy-policy",
            [
                "metaDescriptionKey" => "description_privacy_policy",
                "metaRobotsKey"      => "robots_privacy_policy",
                "pageTitle"          => "generalPrivacyPolicyNoShy",
            ]
        );
    }

    /**
     * Prepare and render the data for the terms and conditions page
     * @return string
     */
    public function showTermsAndConditions():string
    {
        return $this->renderFromConfig(
            "pages.terms_and_conditions",
            "tpl.terms-conditions",
            [
                "metaDescriptionKey" => "description_terms_and_conditions",
                "metaRobotsKey"      => "robots_terms_and_conditions",
                "pageTitle"          => "generalGtc",
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

    private function renderFromConfig( $configKey, $contentTemplateEvent, $metaData )
    {
        /** @var TemplateConfigService $configService */
        $configService = pluginApp( TemplateConfigService::class );
        $categoryId = $configService->get( $configKey, "" );
        $lang = pluginApp(SessionStorageService::class)->getLang();
        $contentTemplate = $this->buildTemplateContainer( $contentTemplateEvent )->getTemplate();

        if ( strlen($categoryId) )
        {
            /** @var CategoryRepositoryContract $categoryRepository */
            $categoryRepository = pluginApp( CategoryRepositoryContract::class );
            $category = $categoryRepository->get(
                $categoryId,
                $lang
            );

            if ( $category !== null )
            {
                return $this->renderCategory( $category, $contentTemplate );
            }
        }

        return $this->renderTemplate(
            "tpl.static-page-container",
            [
                'contentTemplate'   => $contentTemplate,
                'pageTitle'         => $metaData['pageTitle'],
                'metaDescription'   => $metaData['metaDescriptionKey'],
                'metaRobots'        => $metaData['metaRobotsKey']
            ]
        );
    }
}
