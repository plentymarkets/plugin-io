<?php

namespace IO\Services\ContentCaching\Services;

use IO\Services\CheckoutService;
use IO\Services\ContentCaching\Models\SmallContentCache;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Plugin\Storage\Contracts\StorageRepositoryContract;
use Plenty\Plugin\CachingRepository;
use Plenty\Plugin\Templates\Twig;

/**
 * Created by ptopczewski, 14.06.17 10:53
 * Class ContentCaching
 * @package IO\Services\ContentCaching\Services
 */
class ContentCaching
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var CachingRepository
     */
    private $cachingRepository;
    /**
     * @var StorageRepositoryContract
     */
    private $storageRepositoryContract;
    /**
     * @var CheckoutService
     */
    private $checkoutService;
    /**
     * @var CustomerService
     */
    private $customerService;
    /**
     * @var Twig
     */
    private $twig;

    const MAX_BYTE_SIZE_FOR_FAST_CACHING = 524288;
    /**
     * @var SessionStorageService
     */
    private $sessionStorageService; //bytes

    /**
     * ContentCaching constructor.
     * @param Container $container
     * @param Twig $twig
     * @param CachingRepository $cachingRepository
     * @param StorageRepositoryContract $storageRepositoryContract
     * @param CheckoutService $checkoutService
     * @param CustomerService $customerService
     * @param SessionStorageService $sessionStorageService
     */
    public function __construct(
        Container $container,
        Twig $twig,
        CachingRepository $cachingRepository,
        StorageRepositoryContract $storageRepositoryContract,
        CheckoutService $checkoutService,
        CustomerService $customerService,
        SessionStorageService $sessionStorageService
    )
    {
        $this->container                 = $container;
        $this->cachingRepository         = $cachingRepository;
        $this->storageRepositoryContract = $storageRepositoryContract;
        $this->checkoutService           = $checkoutService;
        $this->customerService           = $customerService;
        $this->twig                      = $twig;
        $this->sessionStorageService     = $sessionStorageService;
    }

    /**
     * @param string $templateName
     * @return string
     */
    public function getContent($templateName): string
    {
        $cachingSettings = $this->container->get($templateName);

        // $cachingHash = '_'.$this->sessionStorageService->getLang().'_'.md5(implode(';', $cachingSettings->getData()));

        // $meta = [
        //     'templateName' => $templateName,
        //     'language' => $this->sessionStorageService->getLang(),
        //     'data' => json_encode($cachingSettings->getData())
        // ];

        // $itemHashFields = null;
        // if ($cachingSettings->containsItems()) {
        //     $itemHashFields = [
        //         'countryId' => $this->checkoutService->getShippingCountryId(),
        //         'currency' => $this->checkoutService->getCurrency(),
        //         'customerClassId' => '',
        //         'referrerId' => 1, //TODO set to real referrer
        //     ];

        //     $meta['itemData'] = json_encode($itemHashFields);

        //     $contact = $this->customerService->getContact();

        //     if ($contact instanceof Contact) {
        //         $itemHashFields['customerClassId'] = $contact->classId;
        //     }

        //     $cachingHash .= '_' . md5(implode('_', $itemHashFields));
        // }

        // $tplName = 'tpl_' . md5($templateName) . $cachingHash;

        // if ($this->cachingRepository->has($tplName)) {
        //     return $this->cachingRepository->get($tplName)->content;
        // }

        // try {
        //     $cachedContentObject = $this->storageRepositoryContract->getObject(
        //         'IO',
        //         $tplName . '.html'
        //     );

        //     if (strlen((STRING)$cachedContentObject->body) <= self::MAX_BYTE_SIZE_FOR_FAST_CACHING) {
        //         $smallContentCache          = pluginApp(SmallContentCache::class);
        //         $smallContentCache->content = $cachedContentObject->body;

        //         $this->cachingRepository->put($tplName, $smallContentCache, 15);
        //     }

        //     return $cachedContentObject->body;

        // } catch (\Exception $exc) {
        // }
        
        $templateContent = $this->twig->render($templateName, ['options' => $cachingSettings->getData()]);

        // $this->storageRepositoryContract->uploadObject(
        //     'IO',
        //     $tplName . '.html',
        //     $templateContent,
        //     false,
        //     $meta
        // );

        return $templateContent;
    }
}