<?php

namespace IO\Extensions\ContentCache;

use Plenty\Modules\Plugin\Events\AfterBuildPlugins;
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
            // TODO: Invalidate content cache
        }
    }
}