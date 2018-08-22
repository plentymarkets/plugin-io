<?php

namespace IO\Extensions\ContentCache;

use Plenty\Modules\ContentCache\Contracts\ContentCacheInvalidationRepositoryContract;
use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
use Plenty\Modules\Plugin\PluginSet\Models\PluginSet;
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
            $pluginSet = $afterBuildPlugins->getPluginSet();
            if($pluginSet instanceof PluginSet)
            {
                foreach($pluginSet->webstores as $webstore)
                {
                    /** @var ContentCacheInvalidationRepositoryContract $contentCacheInvalidationRepo */
                    $contentCacheInvalidationRepo = pluginApp(ContentCacheInvalidationRepositoryContract::class);
                    $contentCacheInvalidationRepo->invalidateAll($webstore->storeIdentifier);
                }
            }
        }
    }
}