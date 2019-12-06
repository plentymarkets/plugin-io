<?php //strict

namespace IO\Api\Resources;

use IO\Builder\Order\AddressType;
use IO\Services\CustomerService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;


class GuestResource extends ApiResource
{
    /** @var CustomerService */
    private $customerService;
    
    public function __construct(Request $request, ApiResponse $response, CustomerService $customerService)
    {
        parent::__construct($request, $response);
        $this->customerService = $customerService;
    }
    
    public function store(): Response
    {
        $email = $this->request->get('email', '');
        
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);
        
        $existingEmail = $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL);
        if (!is_null($existingEmail) && strlen($existingEmail) && $email !== $existingEmail) {
            $addressList[AddressType::BILLING]  = $this->customerService->getAddresses(AddressType::BILLING);
            $addressList[AddressType::DELIVERY] = $this->customerService->getAddresses(AddressType::DELIVERY);
            
            if (count($addressList)) {
                foreach ($addressList as $type => $addresses) {
                    if (count($addresses)) {
                        $this->deleteAddresses($type, $addresses);
                    }
                }
            }
        }
        
        $sessionStorage->setSessionValue(SessionStorageKeys::GUEST_EMAIL, $email);
        
        return $this->response->create($email, ResponseCode::OK);
    }
    
    private function deleteAddresses($type, $addresses)
    {
        foreach ($addresses as $address) {
            $this->customerService->deleteAddress($address->id, $type);
        }
    }
}
