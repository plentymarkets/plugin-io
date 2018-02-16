<?php

namespace IO\Services\ItemSearch\Extensions;

class PriceSearchExtension implements ItemSearchExtension
{

    /**
     * @inheritdoc
     */
    public function getSearch( $parentSearchBuilder )
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        $baseResult['prices'] = [];
        return $baseResult;
    }
}