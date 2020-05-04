<?php

namespace IO\Services;

use Plenty\Plugin\ConfigRepository;

/**
 * Class TemplateConfigService
 * @package IO\Services
 */
class TemplateConfigService
{
    private $configRepository;
    private $templatePluginName;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return $this->configRepository->get($this->templatePluginName .'.'.$key, $default);
    }

    /**
     * @param $key
     * @param bool $default
     * @return mixed
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
     * @param $key
     * @param int $default
     * @return mixed
     */
    public function getInteger($key, $default = 0)
    {
        return intval($this->get($key, $default));
    }
}
