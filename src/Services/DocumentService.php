<?php

namespace IO\Services;

use Plenty\Modules\Document\Contracts\DocumentRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;

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
            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);
            $contactId = $contactRepository->getContactId();
            
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
