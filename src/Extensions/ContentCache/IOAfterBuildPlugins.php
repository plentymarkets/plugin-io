<?php

namespace IO\Extensions\ContentCache;

use Plenty\Modules\ContentCache\Contracts\ContentCacheInvalidationRepositoryContract;
use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

class IOAfterBuildPlugins
{
    use Loggable;

    public function handle(AfterBuildPlugins $afterBuildPlugins)
    {
        $hasCodeChanges = $afterBuildPlugins->sourceHasChanged('IO');
        $hasResourceChanges = $afterBuildPlugins->resourcesHasChanged('IO');

        if ( $hasCodeChanges || $hasResourceChanges )
        {
            /** @var ContentCacheInvalidationRepositoryContract $contentCacheInvalidationRepo */
            $contentCacheInvalidationRepo = pluginApp(ContentCacheInvalidationRepositoryContract::class);
            $contentCacheInvalidationRepo->invalidateAll(pluginApp(Application::class)->getPlentyId()); //TODO plentyId from event
        }
    }
}