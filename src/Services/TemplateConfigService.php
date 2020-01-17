<?php

namespace IO\Services;

use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class TemplateConfigService
 * @package IO\Services
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract
 */
class TemplateConfigService
{
    private $configRepository;
    private $templatePluginName;

    /** @var TemplateConfigRepositoryContract $templateConfigRepo */
    private $templateConfigRepo;

    public function __construct(ConfigRepository $configRepository, TemplateConfigRepositoryContract $templateConfigRepo)
    {
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');

        $this->templateConfigRepo = $templateConfigRepo;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract::get()
     */
    public function get($key, $default = null)
    {
        return $this->templateConfigRepo->get($key, $default);
    }

    /**
     * @param $key
     * @param bool $default
     * @return mixed
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract::getBoolean()
     */
    public function getBoolean($key, $default = false)
    {
        return $this->templateConfigRepo->getBoolean($key, $default);
    }

    /**
     * @param $key
     * @param int $default
     * @return mixed
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract::getInteger()
     */
    public function getInteger($key, $default = 0)
    {
        return $this->templateConfigRepo->getInteger($key, $default);
    }
}
