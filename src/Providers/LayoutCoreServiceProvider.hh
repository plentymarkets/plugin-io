<?hh // strict

namespace LayoutCore\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;


class LayoutCoreServiceProvider extends ServiceProvider
{
    public function register():void
	{
		$this->getApplication()->register(\LayoutCore\Providers\LayoutCoreRouteServiceProvider::class);

        $this->getApplication()->singleton('LayoutCore\Helper\TemplateContainer');

        $this->getApplication()->bind('LayoutCore\Builder\Item\ItemColumnBuilder');
        $this->getApplication()->bind('LayoutCore\Builder\Item\ItemFilterBuilder');
        $this->getApplication()->bind('LayoutCore\Builder\Item\ItemParamsBuilder');

        $this->getApplication()->singleton('LayoutCore\Services\NavigationService');
        $this->getApplication()->singleton('LayoutCore\Services\CategoryService');

	}

	public function boot( Twig $twig ):void
	{
        $twig->addExtension(\LayoutCore\Extensions\TwigServiceProvider::class);
        $twig->addExtension(\LayoutCore\Extensions\TwigLayoutCoreExtension::class);
        $twig->addExtension('Twig_Extensions_Extension_Intl');
	}
}
