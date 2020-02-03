<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

/**
 * Class PluginConfig
 * @package IO\Helper
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Helpers\PluginConfig
 */
class PluginConfig
{
    private $pluginName;

    /** @var ConfigRepository $configRepository */
    private $configRepository;

    public function __construct( ConfigRepository $configRepository, $pluginName )
    {
        $this->pluginName = $pluginName;
        $this->configRepository = $configRepository;
    }

    /**
     * @param string $key
     * @param array $possibleValues
     * @param null $default
     * @return array|null
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getMultiSelectValue()
     */
    protected function getMultiSelectValue( $key, $possibleValues = [], $default = null )
    {
        $configValue = $this->getConfigValue( $key, "all" );
        if ( $configValue === "all" )
        {
            return $possibleValues;
        }
        elseif ( !strlen( $configValue ) )
        {
            if ( $default === null )
            {
                return $possibleValues;
            }
            else
            {
                return $default;
            }
        }
        else
        {
            return explode( ", ", $configValue );
        }
    }

    /**
     * @param string $key
     * @param string $default
     * @param string $transformDefault
     * @return mixed|string
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getTextValue()
     */
    protected function getTextValue( $key, $default = "", $transformDefault = "" )
    {
        $value = $this->getConfigValue( $key, $default );
        if ($value === $transformDefault)
        {
            return $default;
        }
        return $value;
    }

    /**
     * @param string $key
     * @param int $default
     * @return int
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getIntegerValue()
     */
    protected function getIntegerValue( $key, $default = 0 )
    {
        return intval( $this->getConfigValue( $key, $default) );
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getBooleanValue()
     */
    protected function getBooleanValue( $key, $default = false )
    {
        $value = $this->getConfigValue($key);

        if ( $value === "true" || $value === "false"  || $value === "1" || $value === "0" || $value === 1 || $value === 0)
        {
            return $value === "true" || $value === "1" || $value === 1;
        }

        return $default;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getConfigValue()
     */
    protected function getConfigValue( $key, $default = null )
    {
        return $this->configRepository->get( $this->pluginName . "." . $key, $default );
    }
}
