<?php

namespace IO\Services\ItemSearch\Extensions;

use Plenty\Modules\ContentCache\Contracts\ContentCacheRepositoryContract;

class ContentCacheVariationLinkExtension implements ItemSearchExtension
{
    public function getSearch($parentSearchBuilder)
    {
        return null;
    }
    
    public function transformResult($baseResult, $extensionResult)
    {
        $variationIds = [];
        foreach($baseResult['documents'] as $variation)
        {
            $variationIds[] = (int)$variation['id'];
        }
    
        /** @var ContentCacheRepositoryContract $contentCacheRepo */
        $contentCacheRepo = pluginApp(ContentCacheRepositoryContract::class);
        $contentCacheRepo->linkVariationsToResponse($variationIds);
        
        return $baseResult;
    }
}