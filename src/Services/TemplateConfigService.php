<?php

namespace IO\Services;

use Plenty\Plugin\ConfigRepository;

/**
 * Service Class TemplateConfigService
 *
 * This service class contains functions related to template configuration functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class TemplateConfigService
{
    private $configRepository;
    private $templatePluginName;

    /**
     * TemplateConfigService constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');
    }

    /**
     * Get a config value by it's key
     * @param string $key Key of the config value
     * @param mixed $default Optional: A default to be returned, if no value for given key is set (Default: null)
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return $this->configRepository->get($this->templatePluginName .'.'.$key, $default);
    }

    /**
     * Get a config value by it's key as a boolean
     * @param string $key Key of the config value
     * @param bool $default Optional: A default to be returned, if no value for given key is set (Default: false)
     * @return bool
     */
    public function getBoolean($key, $default = false)
    {
        $value = $this->get($key);

        if ( $value === "true" || $value === "false"  || $value === "1" || $value === "0" || $value === 1 || $value === 0)
        {
            return $value === "true" || $value === "1" || $value === 1;
        }

        return $default;
    }

    /**
     * Get a config value by it's key as an integer
     * @param string $key Key of the config value
     * @param int $default Optional: A default to be returned, if no value for given key is set (Default: 0)
     * @return int
     */
    public function getInteger($key, $default = 0)
    {
        return intval($this->get($key, $default));
    }
}
