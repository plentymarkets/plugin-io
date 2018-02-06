<?php // strict

namespace IO\Providers;

use IO\Extensions\Facets\AvailabilityFacet;
use IO\Extensions\TwigIOExtension;
use IO\Extensions\TwigServiceProvider;
use IO\Middlewares\Middleware;
use IO\Services\ContentCaching\ContentCachingProvider;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Factories\ItemLoaderFactoryES;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\NotificationService;
use IO\Services\TemplateConfigService;
use Plenty\Plugin\Events\Dispatcher;
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
        $this->getApplication()->register(ContentCachingProvider::class);
        $this->addGlobalMiddleware(Middleware::class);
        $this->getApplication()->register(IORouteServiceProvider::class);

        $this->getApplication()->singleton('IO\Helper\TemplateContainer');

        $this->getApplication()->bind('IO\Builder\Item\ItemColumnBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemFilterBuilder');
        $this->getApplication()->bind('IO\Builder\Item\ItemParamsBuilder');

        $this->getApplication()->singleton('IO\Services\CategoryService');

        $this->getApplication()->singleton(NotificationService::class);


        //TODO check ES ready state
        $this->getApplication()->bind(ItemLoaderFactory::class, ItemLoaderFactoryES::class);
        $this->getApplication()->singleton(FacetExtensionContainer::class);
    }
    
    /**
     * boot twig extensions and services
     * @param Twig $twig
     * @param Dispatcher $dispatcher
     * @param TemplateConfigService $templateConfigService
     */
    public function boot(Twig $twig, Dispatcher $dispatcher, TemplateConfigService $templateConfigService)
    {
        $twig->addExtension(TwigServiceProvider::class);
        $twig->addExtension(TwigIOExtension::class);
        $twig->addExtension('Twig_Extensions_Extension_Intl');
        $twig->addExtension(TwigLoaderPresets::class);
    
        //add availability facet extension if active in template config
        if($templateConfigService->get('filter.availabilityFilterActive') == 'true')
        {
            $dispatcher->listen('IO.initFacetExtensions', function (FacetExtensionContainer $facetExtensionContainer) {
                $facetExtensionContainer->addFacetExtension(pluginApp(AvailabilityFacet::class));
            });
        }
    }
}
