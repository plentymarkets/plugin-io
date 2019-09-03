<?php

namespace IO\Extensions\Sitemap;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\Services\PluginSeoSitemapService;
use Plenty\Plugin\ConfigRepository;

class IOSitemapPattern
{
    /**
     * @param LoadSitemapPattern $sitemapPattern
     */
    public function handle(LoadSitemapPattern $sitemapPattern)
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
        }else
        {
            $itemPattern = [
                'onlyMainVariation' => $templateConfigService->get('item.variation_show_type', 'all') === 'main'
            ];
        }

        $seoSitemapService->setItemPattern($itemPattern);
    }
}
