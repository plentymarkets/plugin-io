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
}