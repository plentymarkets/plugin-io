<?php

namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields;
/**
 * Class ResultFieldTemplate
 * Emit events to request templates to be used for result fields.
 * @package IO\Services\ItemSearch\Helper
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate
 */
class ResultFieldTemplate
{
    use LoadResultFields;

    const TEMPLATE_LIST_ITEM    = 'IO.ResultFields.ListItem';
    const TEMPLATE_SINGLE_ITEM  = 'IO.ResultFields.SingleItem';
    const TEMPLATE_BASKET_ITEM  = 'IO.ResultFields.BasketItem';
    const TEMPLATE_AUTOCOMPLETE_ITEM_LIST = 'IO.ResultFields.AutoCompleteListItem';
    const TEMPLATE_CATEGORY_TREE = 'IO.ResultFields.CategoryTree';
    const TEMPLATE_VARIATION_ATTRIBUTE_MAP = 'IO.ResultFields.VariationAttributeMap';

    private $templates = [];
    private $requiredFields = [];

    private static function init( $template )
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp( Dispatcher::class );

        /** @var ResultFieldTemplate $container */
        $container = pluginApp( ResultFieldTemplate::class );
        $dispatcher->fire( $template, [$container] );

        return $container;
    }

    /**
     * Get the path to result fields file from template/ theme
     * @param string    $template   Event to be emitted to templates/ themes
     * @return string
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     */
    public static function get( $template )
    {
        $container = self::init( $template );

        return $container->templates[$template];
    }

    /**
     * @param string $template
     * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     */
    public static function load( $template )
    {
        $container = self::init( $template );

        $resultFields = $container->loadResultFields($container->templates[$template]);

        foreach($container->requiredFields[$template] ?? [] as $requiredField)
        {
            foreach($resultFields as $resultField)
            {
                $isWildcard = substr($resultField, strlen($resultField) - 1, 1 ) === "*";
                $includesField = strpos($requiredField, substr($resultField, 0, strlen($resultField) - 1)) === 0;
                if($resultField === $requiredField || ($isWildcard && $includesField))
                {
                    break;
                }
            }
            $resultFields[] = $requiredField;
        }

        return $resultFields;
    }

    /**
     * Set the path of a template to read result fields from.
     *
     * @param string    $event      The event to set the template for.
     * @param string    $template   Path to the template to read result fields from.
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     */
    public function setTemplate( $event, $template )
    {
        $this->templates[$event] = $template;
    }

    /**
     * Set multiple templates to read result fields from.
     * @param object $templateMap
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     */
    public function setTemplates( $templateMap )
    {
        foreach( $templateMap as $event => $template )
        {
            $this->setTemplate( $event, $template );
        }
    }

    /**
     * Add required fields to variation search requests.
     * Required fields are independent of the loaded result fields template and will be loaded for sure.
     *
     * @param string|array  $event      A single template event to set required fields for
     *                                  or a map between template events and list of required fields
     * @param string|array  $field      If first parameter describes a single template event
     *                                  this parameter may contain a single result field or a list of field to require.
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     */
    public function requireFields( $event, $field = null )
    {
        if( is_string($event) )
        {
            $this->requiredFields[$event] = $this->requiredFields[$event] ?? [];
            if ( is_string($field) )
            {
                $this->requiredFields[$event][] = $field;
            }
            else if ( is_array($field) )
            {
                $this->requiredFields[$event] = array_merge($this->requiredFields[$event], (array) $field);
            }
        }
        else if( is_array($event) && is_null($field) )
        {
            foreach((array) $event as $evt => $fieldList)
            {
                $this->requireFields($evt, $fieldList);
            }
        }
    }
}
