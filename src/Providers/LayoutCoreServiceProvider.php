<?php // strict

namespace LayoutCore\Providers;

use LayoutCore\Extensions\TwigLayoutCoreExtension;
use LayoutCore\Extensions\TwigServiceProvider;
use LayoutCore\Guards\AbstractGuard;
use LayoutCore\Helper\AbstractFactory;
use LayoutCore\Providers\LayoutCoreRouteServiceProvider;
use LayoutCore\Services\NotificationService;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

/**
 * Class LayoutCoreServiceProvider
 * @package LayoutCore\Providers
 */
class LayoutCoreServiceProvider extends ServiceProvider
{
    /**
     * Register the core functions
     */
	public function register()
	{
        AbstractFactory::$application = $this->getApplication();

		$this->getApplication()->register(LayoutCoreRouteServiceProvider::class);

		$this->getApplication()->singleton('LayoutCore\Helper\TemplateContainer');

		$this->getApplication()->bind('LayoutCore\Builder\Item\ItemColumnBuilder');
		$this->getApplication()->bind('LayoutCore\Builder\Item\ItemFilterBuilder');
		$this->getApplication()->bind('LayoutCore\Builder\Item\ItemParamsBuilder');

		$this->getApplication()->singleton('LayoutCore\Services\CategoryService');

        $this->getApplication()->singleton(NotificationService::class);
	}

    /**
     * boot twig extensions and services
     * @param Twig $twig
     */
	public function boot(Twig $twig)
	{
		$twig->addExtension(TwigServiceProvider::class);
		$twig->addExtension(TwigLayoutCoreExtension::class);
		$twig->addExtension('Twig_Extensions_Extension_Intl');
	}
}
