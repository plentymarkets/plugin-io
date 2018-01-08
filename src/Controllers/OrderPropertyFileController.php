<?php

namespace IO\Controllers;

use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Plugin\Http\Response;

class OrderPropertyFileController extends LayoutController
{
    public function showFile1(string $hash1, string $filename)
    {
        if(strlen($hash1) && strlen($filename))
        {
            /** @var OrderPropertyFileService $orderPropertyFileService */
            $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);
            
            $key = $hash1.'/'.$filename;
            
            $url = $orderPropertyFileService->getFileURL($key);
            
            return pluginApp(Response::class)->redirectTo($url);
        }
    
        return $this->renderTemplate(
            "tpl.page-not-found",
            [
                "data" => ''
            ]
        );
    }
    
    public function showFile2(string $hash1, string $hash2 = '', string $filename)
    {
        if(strlen($hash1) && strlen($filename))
        {
            /** @var OrderPropertyFileService $orderPropertyFileService */
            $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);
            
            $key = $hash1.'/';
            if(strlen($hash2))
            {
                $key .= $hash2.'/';
            }
            $key .= $filename;
            
            $url = $orderPropertyFileService->getFileURL($key);
            
            return pluginApp(Response::class)->redirectTo($url);
        }
        
        return $this->renderTemplate(
            "tpl.page-not-found",
            [
                "data" => ''
            ]
        );
    }
}