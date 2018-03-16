<?php

namespace IO\Extensions\Sitemap;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\Services\PluginSeoSitemapService;
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
        /** @var PluginRepositoryContract $pluginRepository */
        $pluginRepository = pluginApp(PluginRepositoryContract::class);
        
        $pluginList = $pluginRepository->searchPlugins(['name' => 'IO'])->getResult();
        $plugin = $pluginList[0];
        
        if($plugin instanceof Plugin && $plugin->activeProductive)
        {
            /** @var PluginSeoSitemapService $seoSitemapService */
            $seoSitemapService = pluginApp(PluginSeoSitemapService::class);
    
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $enableOldURLPattern = $templateConfigService->get('global.enableOldUrlPattern');
    
            if(!strlen($enableOldURLPattern) || $enableOldURLPattern == 'false')
            {
                $itemPattern = [
                    'pattern' => '_{itemId}_{variationId?}',
                    'container' => ''
                ];
        
                $seoSitemapService->setItemPattern($itemPattern);
            }
    
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
}