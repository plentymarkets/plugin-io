<?php

namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Log\Loggable;


/**
 * Class ResultFieldTemplate
 *
 * Emit events to request templates to be used for result fields.
 *
 * @package IO\Services\ItemSearch\Helper
 */
class ResultFieldTemplate
{
    use Loggable;
    const TEMPLATE_LIST_ITEM    = 'IO.ResultFields.ListItem';
    const TEMPLATE_SINGLE_ITEM  = 'IO.ResultFields.SingleItem';
    const TEMPLATE_BASKET_ITEM  = 'IO.ResultFields.BasketItem';
    const TEMPLATE_AUTOCOMPLETE_ITEM_LIST = 'IO.ResultFields.AutoCompleteListItem';
    const TEMPLATE_CATEGORY_TREE = 'IO.ResultFields.CategoryTree';

    private $templates = [];

    /**
     * Get the path to result fields file from template/ theme
     *
     * @param string    $template   Event to be emitted to templates/ themes
     *
     * @return string
     */
    public static function get( $template )
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp( Dispatcher::class );

        /** @var ResultFieldTemplate $container */
        $container = pluginApp( ResultFieldTemplate::class );
        $dispatcher->fire( $template, [$container] );
        $this->getLogger(__METHOD__)->error("get Result", $container->templates[$template]);
        return $container->templates[$template];
    }

    /**
     * Set the path of a template to read result fields from.
     *
     * @param string    $event      The event to set the template for.
     * @param string    $template   Path to the template to read result fields from.
     */
    public function setTemplate( $event, $template )
    {
        $this->templates[$event] = $template;
    }

    /**
     * Set multiple templates to read result fields from.
     *
     * @param $templateMap
     */
    public function setTemplates( $templateMap )
    {
        foreach( $templateMap as $event => $template )
        {
            $this->setTemplate( $event, $template );
        }
    }
}
