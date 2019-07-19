<?php

namespace IO\Controllers;

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
}