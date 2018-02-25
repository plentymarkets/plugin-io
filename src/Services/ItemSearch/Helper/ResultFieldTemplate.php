<?php

namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Events\Dispatcher;

class ResultFieldTemplate
{
    const TEMPLATE_LIST_ITEM    = 'IO.ResultFields.ListItem';
    const TEMPLATE_SINGLE_ITEM  = 'IO.ResultFields.SingleItem';
    const TEMPLATE_BASKET_ITEM  = 'IO.ResultFields.BasketItem';

    private $templates = [];

    public static function get( $template )
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp( Dispatcher::class );

        /** @var ResultFieldTemplate $container */
        $container = pluginApp( ResultFieldTemplate::class );
        $dispatcher->fire( $template, [$container] );

        return $container->templates[$template];
    }

    public function setTemplate( $event, $template )
    {
        $this->templates[$event] = $template;
    }

    public function setTemplates( $templateMap )
    {
        foreach( $templateMap as $event => $template )
        {
            $this->setTemplate( $event, $template );
        }
    }
}