<?php

namespace IO\Services;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Translation\Translator;

class TemplateConfigService
{
    private $configRepository;
    private $templatePluginName;
    private $translator;
    
    public function __construct(ConfigRepository $configRepository, Translator $translator)
    {
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');
        $this->translator = $translator;
    }
    
    public function get($key, $default = null)
    {
        if(strlen($this->templatePluginName))
        {
            return $this->configRepository->get($this->templatePluginName.'.'.$key, $default);
        }
        
        return null;
    }
    
    public function getTranslation($key, $params = [])
    {
        return $this->translator->trans($this->templatePluginName.'::'.$key, $params);
    }
    
    public function getTemplatePluginName()
    {
        return $this->templatePluginName;
    }
}