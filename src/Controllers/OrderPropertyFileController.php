<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Middlewares\CheckNotFound;
use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;

class OrderPropertyFileController extends LayoutController
{
    public function downloadTempFile(string $hash)
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        $filename = $request->get('filename', '');

        if (strlen($hash) && strlen($filename)) {
            $key = $hash.'/'.$filename;
            $response = $this->download($key);
        }

        if (!$response instanceof Response) {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);
            CheckNotFound::$FORCE_404 = true;
        }
        return $response;
    }

    public function downloadFile(string $hash1, string $hash2 = '')
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);
        $filename = $request->get('filename', '');

        if (strlen($hash1) && strlen($filename)) {
            $key = $hash1.'/';
            if(strlen($hash2))
            {
                $key .= $hash2.'/';
            }
            $key .= $filename;
            $response = $this->download($key);
        }

        if (!$response instanceof Response) {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);
            CheckNotFound::$FORCE_404 = true;
        }
        return $response;
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

            /** @var Response $response */
            $response = pluginApp(Response::class);
            return $response->make($objectFile->body, 200,
                [
                    'Content-Type' => $headerData['content-type'],
                    'Content-Length' => $headerData['content-length']
                ]
            );
        }
    }
}