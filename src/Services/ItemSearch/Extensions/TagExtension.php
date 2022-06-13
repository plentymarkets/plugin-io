<?php

namespace IO\Services\ItemSearch\Extensions;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Contracts\TagRepositoryContract;

class TagExtension implements ItemSearchExtension
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
        $tagIds = $this->getTagIds($baseResult);
        if(!count($tagIds))
        {
            return $baseResult;
        }

        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);

        $tags = $authHelper->processUnguarded( function() use ($tagIds)
        {
            /** @var $tagRepositoryContract TagRepositoryContract */
            $tagRepositoryContract = pluginApp(TagRepositoryContract::class);
            return $tagRepositoryContract->getTagsByIds($tagIds);
        });

        foreach($tags as $tag)
        {
            foreach($baseResult['documents'] as $documentIndex => $baseResultDocument )
            {
                foreach($baseResultDocument['data']['tags'] as $tagsIndex => $baseResultTag)
                {
                    if($baseResultTag['id'] === $tag['id'])
                    {
                        $baseResult['documents'][$documentIndex]['data']['tags'][$tagsIndex]['color'] = $tag['color'];
                    }
                }
            }
        }
        return $baseResult;
    }

    private function getTagIds($baseResult)
    {
        $tagIds = [];
        if(is_array($baseResult['documents']) && count($baseResult['documents']))
        {
            foreach($baseResult['documents'] as $item)
            {
               foreach($item['data']['tags'] as $tag)
               {
                   array_push($tagIds, $tag['id']);
               }
            }
        }
        return array_unique($tagIds);
    }
}
