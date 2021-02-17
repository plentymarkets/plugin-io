<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ContactBankService;

/**
 * Class ContactBankResource
 *
 * Resource class for the route `io/customer/bank_data`.
 * @package IO\Api\Resources
 */
class ContactBankResource extends ApiResource
{
    /**
     * @var ContactBankService $contactBankService Instance of the ContactBankService.
     */
    private $contactBankService;

    /**
     * ContactBankResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ContactBankService $contactBankService
     */
    public function __construct( Request $request, ApiResponse $response, ContactBankService $contactBankService )
    {
        parent::__construct( $request, $response );
        $this->contactBankService = $contactBankService;
    }

    /**
     * Find a ContactBank model by ID.
     * @param string $contactBankId ID of ContactBank to find.
     * @return Response
     */
    public function show( string $contactBankId ):Response
    {
        $response = $this->contactBankService->findContactBankById((int)$contactBankId);
        return $this->response->create($response, ResponseCode::OK);
    }

    /**
     * Create a new bank account for a contact and return it.
     * @return Response
     */
    public function store():Response
    {
        $requestData = $this->request->all();
        $response = $this->contactBankService->createContactBank($requestData);
        return $this->response->create( $response, ResponseCode::CREATED );
    }

    /**
     * Delete a bank account.
     * @param string $contactBankId ID of the ContactBank model to be deleted.
     * @return Response
     */
    public function destroy( string $contactBankId ):Response
    {
        $response = $this->contactBankService->deleteContactBank((int)$contactBankId);
        return $this->response->create( $response, ResponseCode::OK );
    }

    /**
     * Update a bank account.
     * @param string $contactBankId ID of the ContactBank model to update.
     * @return Response
     */
    public function update( string $contactBankId ):Response
    {
        $requestData = $this->request->all();
        $response = $this->contactBankService->updateContactBank( $requestData, (int) $contactBankId );
        return $this->response->create( $response, ResponseCode::OK );
    }
}
