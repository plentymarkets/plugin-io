<?php

namespace IO\Controllers;

use IO\Helper\RouteConfig;

class NewsletterOptOutController extends LayoutController
{
    public function showOptOut()
    {
        return $this->renderTemplate(
            'tpl.newsletter.opt-out',
            ['data' => ''],
            false
        );
    }

    public function redirect()
    {
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectToCategory(RouteConfig::getCategoryId(RouteConfig::NEWSLETTER_OPT_OUT), '/newsletter/unsubscribe');
    }
}
