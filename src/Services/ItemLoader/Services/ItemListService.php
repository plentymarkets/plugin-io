<?php

namespace IO\Services\ItemLoader\Services;

use IO\Services\ItemLoader\Loaders\CrossSellingItems;
use IO\Services\TemplateConfigService;
use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;
use IO\Services\ItemLoader\Loaders\LastSeenItemList;
use IO\Services\ItemLoader\Loaders\TagItems;

class ItemListService
{
    private $templateConfigService;
    private $twigLoaderPresets;
    
    public function __construct(TemplateConfigService $templateConfigService, TwigLoaderPresets $twigLoaderPresets)
    {
        $this->templateConfigService =  $templateConfigService;
        $this->twigLoaderPresets = $twigLoaderPresets;
    }
    
    public function getLoaderClassListForSingleItem()
    {
        $loaderClassList = [
            'single' => [
                SingleItem::class,
                SingleItemAttributes::class,
            ]
        ];
        
        $lastSeenItems = $this->getLastSeenItems();
        if(count($lastSeenItems))
        {
            $loaderClassList['multi']['LastSeenItemsList'] = $lastSeenItems;
        }
    
        $tagItemsList = $this->getTagsItems();
        if(count($tagItemsList))
        {
            $loaderClassList['multi']['TagItemsList'] = $tagItemsList;
        }
        
        $crossSellingItems = $this->getCrossSellingItems();
        if(count($crossSellingItems))
        {
            foreach($crossSellingItems as $name => $loader)
            {
                $loaderClassList['multi'][$name] = $loader;
            }
        }
        
        return $loaderClassList;
    }
    
    private function getLastSeenItems()
    {
        $result = [];
        if($this->templateConfigService->get('item.lists.intern.show_last_seen') == 'true')
        {
            $result = LastSeenItemList::class;
        }
        
        return $result;
    }
    
    private function getTagsItems()
    {
        $result = [];
        if($this->templateConfigService->get('item.lists.intern.show_tag_list') == 'true')
        {
            $loaderOptions = [];
            $tagIdString = $this->templateConfigService->get('item.lists.intern.tag_ids');
            if(strlen($tagIdString))
            {
                $tagIds = explode(',', $tagIdString);
                if(count($tagIds))
                {
                    foreach($tagIds as $tagId)
                    {
                        $loaderOptions['tagIds'][] = (int)$tagId;
                    }
                }
                
            }
            
            $result = [TagItems::class, $loaderOptions];
        }
    
        return $result;
    }
    
    private function getCrossSellingItems()
    {
        $result = [];
        $crossSellingListString = $this->templateConfigService->get('item.lists.intern.show_cross_lists');
        if(strlen($crossSellingListString))
        {
            $e = explode(',', $crossSellingListString);
            if(count($e))
            {
                foreach($e as $crossSellingType)
                {
                    $type = str_replace('item.lists.intern.cross.', '', trim($crossSellingType));
                    $result['CrossSellingItemsList'.ucfirst($type)] = [CrossSellingItems::class, ['relation' => $type]];
                }
            }
        }
        
        return $result;
    }
}