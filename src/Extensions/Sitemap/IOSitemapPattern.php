<?php

namespace IO\Extensions\Sitemap;

use IO\Helper\RouteConfig;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Plugin\Events\LoadSitemapPattern;
use Plenty\Modules\Plugin\Services\PluginSeoSitemapService;

class IOSitemapPattern
{
    private $contentRoutes = [
        RouteConfig::CANCELLATION_RIGHTS,
        RouteConfig::CANCELLATION_FORM,
        RouteConfig::LEGAL_DISCLOSURE,
        RouteConfig::PRIVACY_POLICY,
        RouteConfig::TERMS_CONDITIONS,
        RouteConfig::CONTACT
    ];

    /**
     * @param LoadSitemapPattern $sitemapPattern
     */
    public function handle(LoadSitemapPattern $sitemapPattern)
    {
        /** @var PluginSeoSitemapService $seoSitemapService */
        $seoSitemapService = pluginApp(PluginSeoSitemapService::class);

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $enableOldURLPattern = $templateConfigService->getBoolean('global.enableOldUrlPattern');

        if (!$enableOldURLPattern) {
            $itemPattern = [
                'pattern' => '_{itemId}_{variationId?}',
                'container' => ''
            ];
        } else {
            $itemPattern = [
                'onlyMainVariation' => $templateConfigService->get('item.variation_show_type', 'all') === 'main'
            ];
        }

        $seoSitemapService->setItemPattern($itemPattern);

        $contentRoutes = [];
        foreach ($this->contentRoutes as $route) {
            if (RouteConfig::isActive($route)) {
                $contentRoutes[] = ['url' => $route];
            }
        }

        if (count($contentRoutes)) {
            $seoSitemapService->setContentCategoryPattern(['pattern' => '', 'container' => $contentRoutes]);
        }
    }
}
