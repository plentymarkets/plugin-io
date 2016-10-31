<?php //strict

namespace LayoutCore\Services;

/**
 * Class TemplateService
 * @package LayoutCore\Services
 */
class TemplateService
{
    public static $currentTemplate = "";

    /**
     * @var CategoryService
     */
    private $categoryService;

    public function __construct( CategoryService $categoryService )
    {
        $this->categoryService = $categoryService;
    }

    public function getCurrentTemplate():string
    {
        return TemplateService::$currentTemplate;
    }

    public function isHome():bool
    {
        return $this->categoryService->isHome();
    }

    public function isMyAccount():bool
    {
        return TemplateService::$currentTemplate == "tpl.my-account";
    }

    public function isCheckout():bool
    {
        return TemplateService::$currentTemplate == "tpl.checkout";
    }
}
