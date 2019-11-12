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
    public function showItemByTag(string $tagName = "", int $tagId = null)
    {
        return $this->renderTemplate(
            'tpl.tags',
            [
                "tagId" => $tagId,
                "tagName" => $tagName
            ]
        );
    }
}
