<?php //strict
namespace LayoutCore\Controllers;

use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Builder\Item\ItemColumnBuilder;
use LayoutCore\Builder\Item\ItemFilterBuilder;
use LayoutCore\Builder\Item\ItemParamsBuilder;
use LayoutCore\Builder\Item\Params\ItemColumnsParams;
use LayoutCore\Builder\Item\Fields\ItemDescriptionFields;
use LayoutCore\Constants\Language;

/**
 * Class BlogController
 * @package LayoutCore\Controllers
 */
class BlogController extends LayoutController
{

    /**
     * Prepare and render the item data.
     * @param ItemDataLayerRepositoryContract $itemRepository repository to search item data
     * @param ItemColumnBuilder $columnBuilder Helper for creating column params for searching items in ItemDataLayerRepositoryContract
     * @param ItemFilterBuilder $filterBuilder Helper for creating item filters for searching items in ItemDataLayerRepositoryContract
     * @param ItemParamsBuilder @paramsBuilder Helper for creating additional params for searching items in ItemDataLayerRepositoryContract
     * @param string $slug The leading part of current request url before a-{itemId}.
     * @param int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
     * @return string
     */
    public function showBlog(
        ItemDataLayerRepositoryContract $itemRepository,
        ItemColumnBuilder $columnBuilder,
        ItemFilterBuilder $filterBuilder,
        ItemParamsBuilder $paramsBuilder,
        string $slug,
        $itemId = null
    ):string
    {
        if($itemId === null)
        {
            // If request URL does not contain a slug, e.g.:
            // master.plentymarkets.com/a-123
            // => get $itemId from $slug
            $itemId = (int)$slug;
        }

        // Define the required fields to get from data base
        $columns = $columnBuilder
            ->withItemDescription([
                                      ItemDescriptionFields::NAME_1,
                                      ItemDescriptionFields::DESCRIPTION
                                  ])
            ->build();

        // Filter the current item by item ID
        $filter = $filterBuilder
            ->hasId([$itemId])
            ->build();

        // Set parameters
        // TODO: make current language global
        $params = $paramsBuilder
            ->withParam(ItemColumnsParams::LANGUAGE, Language::DE)
            ->build();

        $currentItem = $itemRepository->search(
            $columns,
            $filter,
            $params
        )->current();

        // Render the template; i.e. LayoutController
        return $this->renderTemplate(
            "tpl.category.blog",
            [
                "item" => $currentItem
            ]
        );
    }
}
