<?hh //strict

namespace LayoutCore\Builder\Order;

use Plenty\Plugin\Application;
use Plenty\Modules\Basket\Models\Basket;
use LayoutCore\Services\BasketService;

class OrderBuilder
{
    private Application $app;
    private BasketService $basketService;

    public function __construct( Application $app, BasketService $basketService )
    {
        $this->app = $app;
        $this->basketService = $basketService;
    }

    public function prepare( OrderType $type, ?int $plentyId = $this->app->getPlentyId() ):OrderBuilderQuery
    {
        $instance = $this->app->make(
            OrderBuilderQuery::class,
            [
                "app" => $this->app,
                "basketService" => $this->basketService,
                "type" => (int) $type,
                "plentyId" => $plentyId
            ]
        );

        invariant( $instance instanceof OrderBuilderQuery, "Error while instantiating OrderBuilderQuery" );
        return $instance;
    }

}

class OrderBuilderQuery
{
    private array<string, mixed> $order;
    private Application $app;
    private BasketService $basketService;

    public function __construct( Application $app, BasketService $basketService, int $type, int $plentyId )
    {
        $this->app = $app;
        $this->basketService = $basketService;

        $this->order = array();
        $this->order["typeId"] = $type;
        $this->order["plentyId"] = $plentyId;
    }

    public function done():array<string, mixed>
    {
        return $this->order;
    }

    public function fromBasket( ?Basket $basket = null ):OrderBuilderQuery
    {
        if( $basket === null )
        {
            $basket = $this->basketService->getBasket();
        }

        // add basket items to order
        $orderItemBuilder = $this->app->make( OrderItemBuilder::class );
        invariant( $orderItemBuilder instanceof OrderItemBuilder, "Error while instantiating OrderItemBuilder." );

        $items = $this->basketService->getBasketItems()["items"];
        invariant( is_array( $items ), "Error while reading item data from basket");

        $this->withOrderItems(
            $orderItemBuilder->fromBasket( $basket, $items )
        );

        return $this;
    }

    public function withStatus( float $status ):OrderBuilderQuery
    {
        $this->order["statusId"] = $status;
        return $this;
    }

    public function withOwner( int $ownerId ):OrderBuilderQuery
    {
        $this->order["ownerId"] = $ownerId;
        return $this;
    }

    public function withOrderItem( array<string, mixed> $orderItem ):OrderBuilderQuery
    {
        if( $this->order["orderItems"] === null )
        {
            $this->order["orderItems"] = array();
        }
        array_push( $this->order["orderItems"], $orderItem );

        return $this;
    }

    public function withOrderItems( array<array<string, mixed>> $orderItems ):OrderBuilderQuery
    {
        foreach( $orderItems as $orderItem )
        {
            $this->withOrderItem( $orderItem );
        }
        return $this;
    }

    public function withAddressId( int $addressId, AddressType $type ):OrderBuilderQuery
    {
        if( $this->order["addresses"] === null )
        {
            $this->order["addresses"] = array();
        }

        $address = [
            "typeId" => (int) $type,
            "addressId" => $addressId
        ];
        array_push( $this->order["addresses"], $address );
        return $this;
    }

    public function withRelation( ReferenceType $type, int $referenceId, RelationType $relationType ):OrderBuilderQuery
    {
        if( $this->order["relations"] === null )
        {
            $this->order["relations"] = array();
        }

        $relation = [
            "referenceType" => (string) $type,
            "referenceId" => $referenceId,
            "relation" => (string) $relationType
        ];

        array_push( $this->order["relations"], $relation );
        return $this;
    }

    public function withContactId( int $customerId ):OrderBuilderQuery
    {
        $this->withRelation( ReferenceType::CONTACT, $customerId, RelationType::RECEIVER );
        return $this;
    }

    public function withOrderOption( OrderOptionType $type, OrderOptionSubType $subType, mixed $value ):OrderBuilderQuery
    {
        if( $this->order["options"] === null )
        {
            $this->order["options"] = array();
        }

        $option = [
            "typeId" => (int) $type,
            "subTypeId" => (int) $subType,
            "value" => $value
        ];

        array_push( $this->order["options"], $option );
        return $this;
    }



}
