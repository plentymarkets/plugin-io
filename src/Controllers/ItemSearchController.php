<?php //strict

namespace IO\Controllers;

class ItemSearchController extends LayoutController
{
    public function showSearch():string
    {
        return $this->renderTemplate(
            "tpl.search",
            [
                "search" => ""
            ]
        );
    }
}