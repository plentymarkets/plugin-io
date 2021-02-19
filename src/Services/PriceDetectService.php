<?php

namespace IO\Services;

use IO\Helper\MemoryCache;
use Plenty\Legacy\Services\Accounting\VatInitService;
use Plenty\Legacy\Services\Item\Variation\DetectSalesPriceService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Accounting\Vat\Contracts\VatInitContract;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Webshop\Contracts\CheckoutRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Repositories\ContactRepository;
use Plenty\Plugin\Application;

/**
 * Service Class PriceDetectService
 *
 * This service class contains functions related to getting the correct sales prices for customers.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\PriceDetectRepositoryContract
 *
 */
class PriceDetectService
{
    use MemoryCache;

    private $classId = null;
    private $singleAccess = null;
    private $currency = null;
    private $plentyId = null;
    private $shippingCountryId = null;

    /**
     * @var DetectSalesPriceService
     */
    private $detectSalesPriceService;

    /**
     * @var ContactRepository $contactRepository
     */
    private $contactRepository;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var CheckoutRepositoryContract $checkoutRepository
     */
    private $checkoutRepository;

    /**
     * @var BasketService $basketService
     */
    private $basketService;

    /**
     * @var VatService $vatService
     */
    private $vatService;

    /**
     * @var VatInitService $vatService
     */
    private $vatInitService;

    private $referrerId;

    /**
     * PriceDetectService constructor.
     * @param DetectSalesPriceService $detectSalesPriceService
     * @param ContactRepositoryContract $contactRepository
     * @param Application $app
     * @param CheckoutRepositoryContract $checkoutRepository
     * @param BasketService $basketService
     * @param VatInitContract $vatInitService
     * @param VatService $vatService
     */
    public function __construct(DetectSalesPriceService $detectSalesPriceService,
                                ContactRepositoryContract $contactRepository,
                                Application $app,
                                CheckoutRepositoryContract $checkoutRepository,
                                BasketService $basketService,
                                VatInitContract $vatInitService,
                                VatService $vatService)
    {
        $this->detectSalesPriceService = $detectSalesPriceService;
        $this->contactRepository = $contactRepository;
        $this->app = $app;
        $this->checkoutRepository = $checkoutRepository;
        $this->basketService = $basketService;
        $this->vatInitService = $vatInitService;
        $this->vatService = $vatService;

        $this->init();
    }

    private function init()
    {
        $contact = $this->contactRepository->getContact();

        if ($contact instanceof Contact) {
            $this->singleAccess = $contact->singleAccess;
        }

        $this->classId = $this->contactRepository->getContactClassId();
        $this->currency = $this->checkoutRepository->getCurrency();
        $this->shippingCountryId = $this->checkoutRepository->getShippingCountryId();
        $this->plentyId = $this->app->getPlentyId();

        $referrerId = (int)$this->basketService->getBasket()->referrerId;
        $this->referrerId = ((int)$referrerId > 0 ? $referrerId : 1);

        if (!$this->vatInitService->isInitialized()) {
            $vat = $this->vatService->getVat();
        }
    }

    /**
     * Get valid price ids for current contact
     *
     * @return array
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\PriceDetectRepositoryContract::getPriceIdsForCustomer()
     */
    public function getPriceIdsForCustomer()
    {
        $accountType = $this->singleAccess;
        $shippingCountryId = $this->shippingCountryId;
        $currency = $this->currency;
        $customerClassId = $this->classId;
        $referrerId = $this->referrerId;
        $plentyId = $this->plentyId;
        $detectSalesPriceService = $this->detectSalesPriceService;

        $priceIds = $this->fromMemoryCache(
            "detectPriceIds.$accountType.$shippingCountryId.$currency.$customerClassId.$referrerId.$plentyId",
            function () use ($accountType, $shippingCountryId, $currency, $customerClassId, $referrerId, $plentyId, $detectSalesPriceService) {
                $detectSalesPriceService->setAccountId(0)
                    ->setAccountType($accountType)
                    ->setCountryOfDelivery($shippingCountryId)
                    ->setCurrency($currency)
                    ->setCustomerClass($customerClassId)
                    ->setOrderReferrer($referrerId)
                    ->setPlentyId($plentyId)
                    ->setQuantity(-1)
                    ->setType(DetectSalesPriceService::PRICE_TYPE_DEFAULT);
                return $detectSalesPriceService->detect();
            }
        );

        return $priceIds;
    }
}
