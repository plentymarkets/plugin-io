<?php

namespace IO\Controllers;

use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Plugin\Http\Response;

class OrderPropertyFileController extends LayoutController
{
    public function showFile(string $hash, string $filename)
    {
        if(strlen($hash) && strlen($filename))
        {
            /** @var OrderPropertyFileService $orderPropertyFileService */
            $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);
            $url = $orderPropertyFileService->getFileURL($hash . '/' . $filename);
            
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