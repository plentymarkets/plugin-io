<?php //strict

namespace IO\Services;

use Illuminate\Database\Eloquent\Collection;
use Plenty\Modules\Account\Contact\Contracts\ContactPaymentRepositoryContract;
use Plenty\Modules\Account\Contact\Models\ContactBank;

/**
 * Class ContactBankService
 *
 * This service class contains methods for manipulating a customers ContactBank model.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ContactBankService
{
    /**
     * @var ContactPaymentRepositoryContract Repository for contacts payment methods
     */
    private $contactPaymentRepository;

    /**
     * ContactBankService constructor.
     * @param ContactPaymentRepositoryContract $contactPaymentRepository Repository for contacts payment methods
     */
    public function __construct(ContactPaymentRepositoryContract $contactPaymentRepository)
    {
        $this->contactPaymentRepository = $contactPaymentRepository;
    }

    /**
     * Get a list of bank accounts of the contact
     *
     * @param int $contactId Id of the contact
     * @param string[] $columns Optional: What columns of the model to return (Default: ['*'])
     * @param int $perPage Optional: Number of bank accounts per page (Default: 50)
     * @return Collection
     */
    public function getBanksOfContact(int $contactId, array $columns = ['*'], int $perPage = 50)
    {
        return $this->contactPaymentRepository->getBanksOfContact($contactId, $columns, $perPage);
    }

    /**
     * Create a new bank account for a contact and return it
     *
     * @param array $data Data for the ContactBank model
     * @return ContactBank
     */
    public function createContactBank(array $data)
    {
        return $this->contactPaymentRepository->createContactBank($data);
    }

    /**
     * Update a ContactBank by id
     *
     * @param array $data The updated data
     * @param int $contactBankId Id of the ContactBank model to update
     * @return ContactBank
     */
    public function updateContactBank(array $data, int $contactBankId)
    {
        return $this->contactPaymentRepository->updateContactBank($data, $contactBankId);
    }

    /**
     * Delete a ContactBank
     *
     * @param int $contactBankId Id of the ContactBank model to be deleted
     * @return bool
     */
    public function deleteContactBank(int $contactBankId): bool
    {
        return $this->contactPaymentRepository->deleteContactBank($contactBankId);
    }

    /**
     * Find a ContactBank model by Id
     *
     * @param int $contactBankId Id of ContactBank to find
     * @return ContactBank
     */
    public function findContactBankById(int $contactBankId): ContactBank
    {
        return $this->contactPaymentRepository->findContactBankById($contactBankId);
    }
}
