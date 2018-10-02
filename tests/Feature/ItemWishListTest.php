<?php

use IO\Services\ItemWishListService;
use IO\Tests\TestCase;

/**
 * User: lukasmatzen
 * Date: 02.10.18
 */
class ItemWishListTest extends TestCase {

    /** @var ItemWishListService $wishListService  */
    protected $wishListService;

    protected function setUp()
    {
        parent::setUp();

        $this->wishListService = pluginApp(ItemWishListService::class);
    }
}