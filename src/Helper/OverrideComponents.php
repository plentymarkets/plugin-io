<?php
namespace IO\Helper;

class OverrideComponents
{
    /**
     * @var array
     */
    private static $overriddenComponents = array();

    public static function overrideComponent(string $componentTag, string $templateId)
    {
        self::$overriddenComponents[$componentTag] = $templateId;
    }
    
    public static function getOverriddenComponents():array
    {
        return self::$overriddenComponents;
    }
}
