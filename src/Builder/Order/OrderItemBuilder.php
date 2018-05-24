<?php //strict

namespace IO\Builder\Order;

use IO\Extensions\Filters\ItemNameFilter;
use IO\Services\SessionStorageService;
use Plenty\Modules\Basket\Models\Basket;
use IO\Services\CheckoutService;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Frontend\Services\OrderPropertyFileService;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Accounting\Vat\Contracts\VatRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\Accounting\Vat\Models\Vat;
/**
 * Class OrderItemBuilder
 * @package IO\Builder\Order
 */
class OrderItemBuilder
{
	/**
	 * @var CheckoutService
	 */
	private $checkoutService;

    /**
     * @var VatService
     */
	private $vatService;

	/** @var ItemNameFilter */
	private $itemNameFilter;

    /**
     * @var VatRepositoryContract
     */
    private $vatRepository;

    /**
     * @var WebstoreRepositoryContract
     */
    private $webstoreRepository;

    /**
     * OrderItemBuilder constructor.
     *
     * @param CheckoutService $checkoutService
     * @param VatService $vatService
     * @param ItemNameFilter $itemNameFilter
     * @param WebstoreRepositoryContract $webstoreRepository
     * @param VatRepositoryContract $vatRepository
     */
	public function __construct(CheckoutService $checkoutService, VatService $vatService, ItemNameFilter $itemNameFilter, WebstoreRepositoryContract $webstoreRepository, VatRepositoryContract $vatRepository)
	{
		$this->checkoutService = $checkoutService;
		$this->vatService = $vatService;
        $this->webstoreRepository = $webstoreRepository;
        $this->vatRepository = $vatRepository;
        $this->itemNameFilter = $itemNameFilter;
	}

	/**
	 * Add a basket item to the order
	 * @param Basket $basket
	 * @param array $items
	 * @return array
	 */
	public function fromBasket(Basket $basket, array $items):array
	{
		$orderItems      = [];
        $maxVatRate      = 0;

        foreach($items as $item)
		{
            if($maxVatRate < $item['vat'])
            {
                $maxVatRate = $item['vat'];
            }

			array_push($orderItems, $this->basketItemToOrderItem($item, $basket->basketRebate));
		}


		// add shipping costs
        $shippingCosts = [
            "typeId"        => OrderItemType::SHIPPING_COSTS,
            "referrerId"    => $basket->basketItems->first()->referrerId,
            "quantity"      => 1,
            "orderItemName" => "shipping costs",
            "countryVatId"  => $this->vatService->getCountryVatId(),
            "vatRate"       => $maxVatRate,
            'vatField'      => $this->getVatField($this->vatService->getVat(), $maxVatRate),
            "amounts"       => [
                [
                    "currency"              => $this->checkoutService->getCurrency(),
                    "priceOriginalGross"    => $basket->shippingAmount
                ]
            ]
        ];
        array_push($orderItems, $shippingCosts);

		$paymentFee = pluginApp(FrontendPaymentMethodRepositoryContract::class)
			->getPaymentMethodFeeById($this->checkoutService->getMethodOfPaymentId());

		$paymentSurcharge = [
			"typeId"        => OrderItemType::PAYMENT_SURCHARGE,
			"referrerId"    => $basket->basketItems->first()->referrerId,
			"quantity"      => 1,
			"orderItemName" => "payment surcharge",
			"countryVatId"  => $this->vatService->getCountryVatId(),
			"vatRate"       => $maxVatRate,
            'vatField'      => $this->getVatField($this->vatService->getVat(), $maxVatRate),
            "amounts"       => [
				[
					"currency"           => $this->checkoutService->getCurrency(),
					"priceOriginalGross" => $paymentFee
				]
			]
		];
		array_push($orderItems, $paymentSurcharge);


		return $orderItems;
	}

	/**
	 * Add a basket item to the order
	 * @param array $basketItem
	 * @return array
	 */
	private function basketItemToOrderItem(array $basketItem, $basketDiscount):array
	{
        $basketItemProperties = [];
        if(count($basketItem['basketItemOrderParams']))
        {
            /** @var OrderPropertyFileService $orderPropertyFileService */
            $orderPropertyFileService = pluginApp(OrderPropertyFileService::class);
            
            foreach($basketItem['basketItemOrderParams'] as $property)
            {
                if($property['type'] == 'file')
                {
                    $file = $orderPropertyFileService->copyBasketFileToOrder($property['value']);
                    $property['value'] = $file;
                }
                
                $basketItemProperty = [
                    'propertyId' => $property['propertyId'],
                    'value'      => $property['value']
                ];
                
                $basketItemProperties[] = $basketItemProperty;
            }
        }
        
		$priceOriginal = $basketItem['variation']['data']['prices']['default']['data']['basePrice'];

        $attributeTotalMarkup = 0;
		if(isset($basketItem['attributeTotalMarkup']))
		{
            $attributeTotalMarkup = $basketItem['attributeTotalMarkup'];
        }
        
        $rebate = 0;
		
        if(isset($basketItem['rebate']))
		{
			$rebate = $basketItem['rebate'];
		}
		
		if((float)$basketDiscount > 0)
        {
            $rebate += $basketDiscount;
        }

		return [
			"typeId"            => OrderItemType::VARIATION,
			"referrerId"        => $basketItem['referrerId'],
			"itemVariationId"   => $basketItem['variationId'],
			"quantity"          => $basketItem['quantity'],
			"orderItemName"     => $this->itemNameFilter->itemName( $basketItem['variation']['data'] ),
			"shippingProfileId" => $basketItem['shippingProfileId'],
			"countryVatId"      => $this->vatService->getCountryVatId(),
			"vatRate"           => $basketItem['vat'],
            "vatField"			=> $this->getVatField($this->vatService->getVat(), $basketItem['vat']),
            "orderProperties"   => $basketItemProperties,
			"amounts"           => [
				[
					"currency"              => $this->checkoutService->getCurrency(),
					"priceOriginalGross"    => $priceOriginal,
                    "surcharge"             => $attributeTotalMarkup,
					"discount"	            => $rebate,
					"isPercentage"          => true
				]
			]
		];
	}

    /**
     * Get the vat field for the given vat rate.
     *
     * @param Vat   $vat		The country VAT instance.
     * @param float $vatRate	The vat rate.
     *
     * @return int
     */
    public function getVatField(Vat $vat, $vatRate)
    {
        $vatRateArray = $vat->vatRates;
        switch($vatRate)
        {
            case $vatRateArray[0]->vatRate:
                return 0;
            case $vatRateArray[1]->vatRate:
                return 1;
            case $vatRateArray[2]->vatRate:
                return 2;
            case $vatRateArray[3]->vatRate:
                return 3;
            default:
                if($vat->isStandard)
                {
                    return 0;
                }
        }
        $storeId = $vat->location->clientId;
        $plentyId = (int)$this->webstoreRepository->findById($storeId)->storeIdentifier;
        $standardVat = $this->vatRepository->getStandardVat($plentyId);
        return $this->getVatField($standardVat, $vatRate);
    }
}
