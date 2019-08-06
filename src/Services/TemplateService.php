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

    public static $currentTemplateData = [];

    public $forceNoIndex = false;

    public function forceNoIndex($forceNoIndex)
    {
        $this->forceNoIndex = $forceNoIndex;
    }

    public function isNoIndexForced()
    {
        return $this->forceNoIndex;
    }

    public function getCurrentTemplate():string
    {
        return TemplateService::$currentTemplate;
    }

    public function setCurrentTemplate($template)
    {
        self::$currentTemplate = $template;
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
        return TemplateService::$currentTemplate == "tpl.home" || TemplateService::$currentTemplate == "tpl.home.category";
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
        return TemplateService::$currentTemplate == "tpl.category.item" || TemplateService::$currentTemplate == "tpl.category.content";
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
