<?php //strict

namespace IO\Controllers;

class TagController extends LayoutController
{
    public function showTag($tagName):string
    {
        return $this->renderTemplate(
            "tpl.search",
            [
                'query' => $tagName
            ],
            false
        );
    }
}