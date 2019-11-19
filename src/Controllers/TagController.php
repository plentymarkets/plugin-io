<?php

namespace IO\Controllers;

/**
 * Class TagController
 * @package IO\Controllers
 */
class TagController extends LayoutController
{
    /**
     * @param string $name
     * @param int $tagId
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
