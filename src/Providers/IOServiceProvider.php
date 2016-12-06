<?php // strict

namespace IO\Providers;

use IO\Extensions\TwigIOExtension;
use IO\Extensions\TwigServiceProvider;
use IO\Services\NotificationService;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

/**
 * Class IOServiceProvider
 * @package IO\Providers
 */
class IOServiceProvider extends ServiceProvider
{
    /**
     * Register the core functions
     */
	public function register()
	{
		$this->getApplication()->register(IORouteServiceProvider::class);

		$this->getApplication()->singleton('IO\Helper\TemplateContainer');

		$this->getApplication()->bind('IO\Builder\Item\ItemColumnBuilder');
		$this->getApplication()->bind('IO\Builder\Item\ItemFilterBuilder');
		$this->getApplication()->bind('IO\Builder\Item\ItemParamsBuilder');

		$this->getApplication()->singleton('IO\Services\CategoryService');

        $this->getApplication()->singleton(NotificationService::class);
	}

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
	public function boot(Twig $twig)
	{
		$twig->addExtension(TwigServiceProvider::class);
		$twig->addExtension(TwigIOExtension::class);
		$twig->addExtension('Twig_Extensions_Extension_Intl');
	}
}
