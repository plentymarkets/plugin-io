<?hh //strict

namespace LayoutCore\Api\Resources;

use Illuminate\Http\Response;
use Plenty\Plugin\Http\Request;
use LayoutCore\Api\ApiResource;
use LayoutCore\Api\ApiResponse;
use LayoutCore\Api\ResponseCode;
use LayoutCore\Services\CustomerService;
use LayoutCore\Builder\Order\AddressType;

class CustomerAddressResource extends ApiResource
{
    private CustomerService $customerService;

    public function __construct( Request $request, ApiResponse $response, CustomerService $customerService )
    {
        parent::__construct( $request, $response );
        $this->customerService = $customerService;
    }

    private function getAddressType():?AddressType
    {
        $typeId = $this->request->get( "typeId", null );
        if( $typeId !== null )
        {
            return AddressType::coerce( $typeId );
        }

        return null;
    }

    public function index():Response
    {
        $type = $this->getAddressType();
        $addresses = $this->customerService->getAddresses( $type );
        return $this->response->create( $addresses, ResponseCode::OK );
    }

    public function store():Response
    {
        $type = $this->getAddressType();
        if( $type === null )
        {
            $this->response->error( 0, "Missing type id." );
            return $this->response->create( null, ResponseCode::BAD_REQUEST );
        }
        $address = $this->customerService->createAddress( $this->request->all(), $type );
        return $this->response->create( $address, ResponseCode::CREATED );
    }

    public function update( string $addressId ):Response
    {
        $type = $this->getAddressType();
        if( $type === null )
        {
            $this->response->error( 0, "Missing type id." );
            return $this->response->create( null, ResponseCode::BAD_REQUEST );
        }

        $addressId = (int) $addressId;
        $address = $this->customerService->updateAddress( $addressId, $this->request->all(), $type );
        return $this->response->create( $address, ResponseCode::OK );
    }

    public function destroy( string $addressId ):Response
    {
        $type = $this->getAddressType();
        if( $type === null )
        {
            $this->response->error( 0, "Missing type id." );
            return $this->response->create( null, ResponseCode::BAD_REQUEST );
        }

        $addressId = (int) $addressId;
        $this->customerService->deleteAddress( $addressId, $type );
        return $this->index();
    }
}
