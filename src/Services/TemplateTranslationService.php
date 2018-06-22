<?php

namespace IO\Services;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Translation\Translator;

class TemplateTranslationService
{
    private $translator;
    private $templatePluginName;

    public function __construct(Translator $translator, ConfigRepository $configRepository)
    {
        $this->translator = $translator;
        $this->templatePluginName = $configRepository->get('IO.template.template_plugin_name');
    }

    public function trans($key, $parameters = [], $locale = null)
    {
        if(strlen($this->templatePluginName))
        {
            return $this->translator->trans($this->templatePluginName.'::'.$key, $parameters, $locale);
        }

        return null;
    }
}