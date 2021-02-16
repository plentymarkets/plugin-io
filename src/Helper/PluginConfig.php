<?php

namespace IO\Helper;

use Plenty\Plugin\ConfigRepository;

/**
 * Class PluginConfig
 *
 * Helper class to access a plugin's configuration.
 *
 * @package IO\Helper
 * @deprecated since 5.0.0 will be removed in 6.0.0.
 * @see \Plenty\Modules\Webshop\Helpers\PluginConfig
 */
class PluginConfig
{
    private $pluginName;

    /** @var ConfigRepository $configRepository */
    private $configRepository;

    /**
     * PluginConfig constructor.
     * @param ConfigRepository $configRepository
     * @param string $pluginName The identifier of the plugin of which the configuration should be loaded.
     */
    public function __construct( ConfigRepository $configRepository, $pluginName )
    {
        $this->pluginName = $pluginName;
        $this->configRepository = $configRepository;
    }

    /**
     * Get an array of values from the config.
     * @param string $key Key for the setting.
     * @param array $possibleValues Possible values for the multi select, will be returned when setting is 'all'.
     * @param array|null $default Default value if setting is empty.
     * @return array|null
     * @deprecated since 5.0.0 will be removed in 6.0.0.
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
     * Get a value as a string from the config.
     * @param string $key Key for the setting.
     * @param string $default Default value if setting is empty.
     * @param string $transformDefault If setting is equal to this, return the default.
     * @return mixed|string
     * @deprecated since 5.0.0 will be removed in 6.0.0.
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
     * Get a value as an integer from the config.
     * @param string $key Key for the setting.
     * @param int $default Default value if setting is empty.
     * @return int
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getIntegerValue()
     */
    protected function getIntegerValue( $key, $default = 0 )
    {
        return intval( $this->getConfigValue( $key, $default) );
    }

    /**
     * Get a value as an boolean from the config.
     * @param string $key Key for the setting.
     * @param bool $default Default value if setting is empty.
     * @return bool
     * @deprecated since 5.0.0 will be removed in 6.0.0.
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
     * Get a value from the config.
     * @param string $key Key for the setting.
     * @param mixed $default Default value if setting is empty.
     * @return mixed
     * @deprecated since 5.0.0 will be removed in 6.0.0.
     * @see \Plenty\Modules\Webshop\Helpers\PluginConfig::getConfigValue()
     */
    protected function getConfigValue( $key, $default = null )
    {
        return $this->configRepository->get( $this->pluginName . "." . $key, $default );
    }
}
