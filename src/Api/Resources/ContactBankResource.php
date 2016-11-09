<?php //strict

namespace LayoutCore\Api\Resources;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\ContactBankService;

class ContactBankResource extends ApiResource
{
    private $contactBankService;

    public function __construct( Request $request, ApiResponse $response, ContactBankService $contactBankService )
    {
        parent::__construct( $request, $response );
        $this->contactBankService = $contactBankService;
    }

    public function show( string $contactBankId ):BaseResponse
    {
        $response = $this->contactBankService->findContactBankById((int)$contactBankId);
        return $this->response->create($response, ResponseCode::OK);
    }

    public function store():BaseResponse
    {
        $requestData = $this->request->all();
        $response = $this->contactBankService->createContactBank($requestData);
        return $this->response->create( $response, ResponseCode::CREATED );
    }

    public function destroy( string $contactBankId ):BaseResponse
    {
        $response = $this->contactBankService->deleteContactBank((int)$contactBankId);
        return $this->response->create( $response, ResponseCode::OK );
    }

    public function update( string $contactBankId ):BaseResponse
    {
        $requestData = $this->request->all();
        $response = $this->contactBankService->updateContactBank( $requestData, (int) $contactBankId );
        return $this->response->create( $response, ResponseCode::OK );
    }
}
