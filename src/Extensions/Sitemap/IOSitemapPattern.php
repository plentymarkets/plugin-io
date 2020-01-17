<?php

namespace IO\Extensions\Sitemap;

use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Services\PluginSeoSitemapService;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;
use Plenty\Plugin\ConfigRepository;

class IOSitemapPattern
{
    private $contentRoutes = [
        'cancellation-rights',
        'cancellation-form',
        'legal-disclosure',
        'privacy-policy',
        'gtc',
        'contact'
    ];
    
    /**
     * @param LoadSitemapPattern $sitemapPattern
     */
    public function handle(LoadSitemapPattern $sitemapPattern)
    {
        /** @var PluginSeoSitemapService $seoSitemapService */
        $seoSitemapService = pluginApp(PluginSeoSitemapService::class);

        /** @var TemplateConfigRepositoryContract $templateConfigRepo */
        $templateConfigRepo = pluginApp(TemplateConfigRepositoryContract::class);
        $enableOldURLPattern = $templateConfigRepo->getBoolean('global.enableOldUrlPattern');

        if(!$enableOldURLPattern)
        {
            $itemPattern = [
                'pattern' => '_{itemId}_{variationId?}',
                'container' => ''
            ];
        }else
        {
            $itemPattern = [
                'onlyMainVariation' => $templateConfigRepo->get('item.variation_show_type', 'all') === 'main'
            ];
        }

        $seoSitemapService->setItemPattern($itemPattern);

        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);

        $contentRoutes = [];
        $enabledRoutes = explode(', ', $configRepository->get('IO.routing.enabled_routes', ''));
        if(count($enabledRoutes))
        {
            foreach($this->contentRoutes as $route)
            {
                if(in_array($route, $enabledRoutes))
                {
                    $contentRoutes[] = ['url' => $route];
                }
            }
        }

        if(count($contentRoutes))
        {
            $seoSitemapService->setContentCategoryPattern(['pattern' => '', 'container' => $contentRoutes]);
        }
    }
}
