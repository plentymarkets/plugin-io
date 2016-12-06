<?php //strict

namespace IO\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactPaymentRepositoryContract;
use Plenty\Modules\Account\Contact\Models\ContactBank;

class ContactBankService
{
    private $contactPaymentRepository;

    public function __construct( ContactPaymentRepositoryContract $contactPaymentRepository )
    {
        $this->contactPaymentRepository = $contactPaymentRepository;
    }

    public function getBanksOfContact(int $contactId, array $columns = ['*'], int $perPage = 50)
    {
        return $this->contactPaymentRepository->getBanksOfContact( $contactId, $columns, $perPage );
    }

    public function createContactBank( array $data)
    {
      return $this->contactPaymentRepository->createContactBank($data);
    }

    public function updateContactBank( array $data, int $contactBankId)
    {
      return $this->contactPaymentRepository->updateContactBank($data, $contactBankId);
    }

    public function deleteContactBank(int $contactBankId):bool
    {
      return $this->contactPaymentRepository->deleteContactBank($contactBankId);
    }

    public function findContactBankById(int $contactBankId):ContactBank
    {
      return $this->contactPaymentRepository->findContactBankById($contactBankId);
    }
}
