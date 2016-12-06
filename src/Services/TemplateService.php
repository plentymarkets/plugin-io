<?php //strict

namespace IO\Services;

/**
 * Class TemplateService
 * @package IO\Services
 */
class TemplateService
{
    public static $currentTemplate = "";

    public function __construct()
    {
        
    }

    public function getCurrentTemplate():string
    {
        return TemplateService::$currentTemplate;
    }

    public function isHome():bool
    {
        return pluginApp(CategoryService::class)->isHome();
    }

    public function isMyAccount():bool
    {
        return TemplateService::$currentTemplate == "tpl.my-account";
    }

    public function isCheckout():bool
    {
        return TemplateService::$currentTemplate == "tpl.checkout";
    }

    public function isSearch():bool
    {
        return TemplateService::$currentTemplate == "tpl.search";
    }
}
