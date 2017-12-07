<?php //strict

namespace IO\Services;

use Plenty\Plugin\Templates\Twig;

/**
 * Class TemplateService
 * @package IO\Services
 */
class TemplateService
{
    public static $currentTemplate = "";

    /** @var WebstoreConfigurationService $webstoreConfigService */
    private $webstoreConfigService;

    public function __construct( WebstoreConfigurationService $webstoreConfigService )
    {
        $this->webstoreConfigService = $webstoreConfigService;
    }

    public function getCanonicalUrl( $lang = "de" )
    {
        $activeLanguages = $this->webstoreConfigService->getActiveLanguageList();
        $prefix = $this->webstoreConfigService->getWebstoreConfig()->domainSsl;

        if ( in_array( $lang, $activeLanguages ) )
        {
            $prefix .=  "/".$lang;

            switch( TemplateService::$currentTemplate )
            {
                case 'tpl.item':
                    break;
                case 'tpl.category.item':
                    /** @var CategoryService $categoryService */
                    $categoryService = pluginApp( CategoryService::class );
                    $category = $categoryService->getCurrentCategory();
                    $categoryDetails = $categoryService->getDetails( $category, $lang );
                    if ( $categoryDetails === null )
                    {
                        return null;
                    }

                    if( strlen( $categoryDetails->canonicalLink ) > 0 )
                    {
                        return $categoryDetails->canonicalLink;
                    }

                    return $prefix . $categoryService->getURL( $category, $lang );
            }

            return $prefix;
        }

        return null;
    }

    public function getCurrentTemplate():string
    {
        return TemplateService::$currentTemplate;
    }

    public function isCurrentTemplate($templateToCheck):bool
    {
        return TemplateService::$currentTemplate == $templateToCheck;
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.home') instead
     */
    public function isHome():bool
    {
        return TemplateService::$currentTemplate == "tpl.home";
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.item') instead
     */
    public function isItem():bool
    {
        return TemplateService::$currentTemplate == "tpl.item";
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.my-account') instead
     */
    public function isMyAccount():bool
    {
        return TemplateService::$currentTemplate == "tpl.my-account";
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.checkout') instead
     */
    public function isCheckout():bool
    {
        return TemplateService::$currentTemplate == "tpl.checkout";
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.search') instead
     */
    public function isSearch():bool
    {
        return TemplateService::$currentTemplate == "tpl.search";
    }

    /**
     * @deprecated use isCurrentTemplate('tpl.category.item') instead
     */
    public function isCategory():bool
    {
        return TemplateService::$currentTemplate == "tpl.category.item";
    }
    
    public function renderTemplate($template, $params)
    {
        $renderedTemplate = '';
    
        if (strlen($template))
        {
            /**
             * @var Twig $twig
             */
            $twig             = pluginApp(Twig::class);
            $renderedTemplate = $twig->render($template, $params);
        }
        
        return $renderedTemplate;
    }
}
