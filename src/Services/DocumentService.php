<?php

namespace IO\Services;

use Plenty\Modules\Document\Contracts\DocumentRepositoryContract;

/**
 * Class DocumentService
 * @package IO\Services
 */
class DocumentService
{
    /** @var DocumentRepositoryContract */
    private $documentRepo;
    
    /**
     * DocumentService constructor.
     * @param DocumentRepositoryContract $documentRepo
     */
    public function __construct(DocumentRepositoryContract $documentRepo)
    {
        $this->documentRepo = $documentRepo;
    }
    
    /**
     * @param $orderId
     * @return null|\Plenty\Repositories\Models\PaginatedResult
     */
    public function getDocumentsByOrderId($orderId)
    {
        $documents = null;
        
        if((int)$orderId > 0)
        {
            /** @var CustomerService $customerService */
            $customerService = pluginApp(CustomerService::class);
            $contactId = $customerService->getContactId();
            
            if((int)$contactId > 0)
            {
                $this->documentRepo->setFilters([
                    'orderId' => $orderId,
                    'contactId' => $contactId
                ]);
                $documents = $this->documentRepo->find();
            }
        }
        
        return $documents;
    }
}