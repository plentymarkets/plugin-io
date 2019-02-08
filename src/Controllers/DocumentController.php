<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Services\CustomerService;
use Plenty\Modules\Cloud\Storage\Models\StorageObject;
use Plenty\Modules\Document\Contracts\DocumentRepositoryContract;
use Plenty\Modules\Document\Models\Document;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Order\Models\Order;

/**
 * Class DocumentController
 * @package IO\Controllers
 */
class DocumentController extends LayoutController
{
    /**
     * @param int $documentId
     * @param Response $response
     * @return Response
     */
    public function download($documentId, Response $response)
    {
        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);
        
        /** @var DocumentRepositoryContract $documentRepo */
        $documentRepo = pluginApp(DocumentRepositoryContract::class);
        $document = $documentRepo->findById($documentId);
        
        $documentStorageObject = null;
        
        if($document instanceof Document)
        {
            /** @var Order $order */
            $order = $document->orders->first();
            
            if($order instanceof Order)
            {
                $orderContactId = $order->relations->where('referenceType', 'contact')->first()->referenceId;
                
                if((int)$orderContactId > 0 && $orderContactId == $customerService->getContactId())
                {
                    //document is matching with the logged in contact
                    $documentStorageObject = $documentRepo->getDocumentStorageObject($document->path);
                }
            }
        }
        
        if($documentStorageObject instanceof StorageObject)
        {
            $response = $response->make(
                $documentStorageObject->body,
                200,
                [
                    'Content-Type' => $documentStorageObject->contentType,
                    'Content-Disposition' => 'inline; filename="' . $document->path . '";',
                    'Content-Length' => $documentStorageObject->contentLength,
                ]
            );
        }
        else
        {
            //document not found or logged in contact not matching
            $response->forceStatus(ResponseCode::NOT_FOUND);
        }
        
        return $response;
    }
}