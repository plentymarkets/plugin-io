<?php

namespace IO\Services;

use Plenty\Modules\Document\Contracts\DocumentRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Service Class DocumentService
 *
 * This service class contains functions related to order documents.
 * All public functions are available in the Twig template renderer.
 *
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
     * Get order documents for a specific order
     * @param int $orderId Unique id of order
     * @return null|PaginatedResult
     */
    public function getDocumentsByOrderId(int $orderId)
    {
        $documents = null;

        if ((int)$orderId > 0) {
            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);
            $contactId = $contactRepository->getContactId();

            if ((int)$contactId > 0) {
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
