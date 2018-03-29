<?php

namespace IO\Controllers;

use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Plugin\Http\Response;

class OrderPropertyFileController extends LayoutController
{
    public function downloadTempFile(string $hash, string $filename)
    {
        if(strlen($hash) && strlen($filename))
        {
            $key = $hash.'/'.$filename;
            return $this->download($key);
        }
    
        return $this->renderTemplate(
            "tpl.page-not-found",
            [
                "data" => ''
            ]
        );
    }
    
    public function downloadFile(string $hash1, string $hash2 = '', string $filename)
    {
        if(strlen($hash1) && strlen($filename))
        {
            $key = $hash1.'/';
            if(strlen($hash2))
            {
                $key .= $hash2.'/';
            }
            $key .= $filename;
            return $this->download($key);
        }
        
        return $this->renderTemplate(
            "tpl.page-not-found",
            [
                "data" => ''
            ]
        );
    }

    /**
     * @param string $key
     * @param integer $orderId
     *
     * @return Response
     */
    private function download($key)
    {
        /** @var OrderPropertyFileService $orderPropertyFileService */
        $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);

        $url = $orderPropertyFileService->getFileURL($key);
        if(!is_null($url) && strlen($url))
        {
            $objectFile = $orderPropertyFileService->getFile($key);
            $headerData = $objectFile->metaData['headers'];

            return pluginApp(Response::class)->make($objectFile->body, 200,
                [
                    'Content-Type' => $headerData['content-type'],
                    'Content-Length' => $headerData['content-length']
                ]
            );
        }
    }
}