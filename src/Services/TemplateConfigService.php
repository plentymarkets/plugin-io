<?php

namespace IO\Services;

use Plenty\Plugin\ConfigRepository;

class TemplateConfigService
{
    private $configRepository;
    private $templatePluginName;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');
    }

    public function get($key, $default = null)
    {
        if(strlen($this->templatePluginName))
        {
            return $this->configRepository->get($this->templatePluginName.'.'.$key, $default);
        }

        return null;
    }

    public function getBoolean($key, $default = false)
    {
        $value = $this->get($key);

        if ( $value === "true" || $value === "false"  || $value === "1" || $value === "0" || $value === 1 || $value === 0)
        {
            return $value === "true" || $value === "1" || $value === 1;
        }

        return $default;
    }

    public function getInteger($key, $default = 0)
    {
        return intval($this->get($key, $default));
    }
}
