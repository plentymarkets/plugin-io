<?php

namespace IO\Controllers;

use IO\Services\ItemListService;

/**
 * Class TagController
 * @package IO\Controllers
 */
class TagController extends LayoutController
{
    /**
     * @param string $name
     * @param int $TagId
     */
    public function showItemByTag(string $tagName = "", int $TagId = null)
    {
        return $this->renderTemplate(
            'tpl.tags',
            [
                "tagId" => $TagId,
                "tagName" => $tagName
            ]
        );
    }
}
