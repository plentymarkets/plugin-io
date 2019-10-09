<?php

namespace IO\Services\ItemSearch\Extensions;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Contracts\TagRepositoryContract;

class TagsExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $tagIds = [];
        if(count($baseResult['documents']))
        {
            foreach($baseResult['documents'] as $item)
            {
               foreach($item['data']['tags'] as $tag)
               {
                   array_push($tagIds, $tag['id']);
               }
            }
        }

        $tags = $authHelper->processUnguarded( function() use ($tagIds)
        {
            /** @var $tagRepositoryContract TagRepositoryContract */
            $tagRepositoryContract = pluginApp(TagRepositoryContract::class);
            return $tagRepositoryContract->getTagsByIds($tagIds);
        });

        foreach($tags as $tag)
        {
            foreach($baseResult['documents'][0]['data']['tags'] as $key => $baseResultTag)
            {
                if($baseResultTag['id'] === $tag['id'])
                {
                    $baseResult['documents'][0]['data']['tags'][$key]['color'] = $tag['color'];
                }
            }
        }
        return $baseResult;
    }
}
