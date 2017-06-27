<?php
namespace IO\Services\ContentCaching\CronJobs;

use IO\Extensions\Functions\Partial;
use IO\Services\CheckoutService;
use IO\Services\ContentCaching\Models\SmallContentCache;
use IO\Services\ContentCaching\Services\Container;
use IO\Services\ContentCaching\Services\ContentCaching;
use IO\Services\SalesPriceService;
use Plenty\Modules\Cloud\Storage\Models\StorageObject;
use Plenty\Modules\Cron\Contracts\CronHandler;
use Plenty\Modules\Frontend\Factories\FrontendFactory;
use Plenty\Modules\Plugin\Storage\Contracts\StorageRepositoryContract;
use Plenty\Plugin\CachingRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;

/**
 * Created by ptopczewski, 16.06.17 16:07
 * Class RebuildContentCache
 * @package IO\Services\ContentCaching\CronJobs
 */
class RebuildContentCache extends CronHandler
{
    use Loggable;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var StorageRepositoryContract
     */
    private $storageRepositoryContract;

    /**
     * @var CheckoutService
     */
    private $checkoutService;

    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var CachingRepository
     */
    private $cachingRepository;

    /**
     * RebuildContentCache constructor.
     * @param Container $container
     * @param CachingRepository $cachingRepository
     * @param StorageRepositoryContract $storageRepositoryContract
     * @param Dispatcher $dispatcher
     * @param CheckoutService $checkoutService
     * @param FrontendFactory $frontendFactory
     * @param Twig $twig
     */
    public function __construct(
        Container $container,
        CachingRepository $cachingRepository,
        StorageRepositoryContract $storageRepositoryContract,
        Dispatcher $dispatcher,
        CheckoutService $checkoutService,
        FrontendFactory $frontendFactory,
        Twig $twig
    )
    {
        $this->container                 = $container;
        $this->storageRepositoryContract = $storageRepositoryContract;
        $this->checkoutService           = $checkoutService;
        $this->twig                      = $twig;

        $dispatcher->fire('IO.init.templates', [pluginApp(Partial::class)]);
        $this->frontendFactory   = $frontendFactory;
        $this->cachingRepository = $cachingRepository;
    }

    /**
     * mandatory handle function
     */
    public function handle()
    {
        echo 'run';
        $objectList = $this->storageRepositoryContract->listObjects(
            'IO',
            'tpl_'
        );

        /** @var SalesPriceService $salesPriceService */
        $salesPriceService = pluginApp(SalesPriceService::class);

        foreach ($objectList->objects as $cacheObject) {
            /** @var StorageObject $cacheObject */

            /** @var StorageObject $object */
            $object = $this->storageRepositoryContract->getObject('IO', 'tpl_' . $cacheObject->key);
            try {
                //check options
                $settings = $this->container->get($object->metaData['templatename']);

                $diff = array_diff_assoc(json_decode($object->metaData['data'], true), $settings->getData());
                if (!empty($diff)) {
                    $this->storageRepositoryContract->deleteObject('IO', 'tpl_' . $cacheObject->key);
                } else {
                    $lang = $object->metaData['language'];
                    $this->frontendFactory->getLocale()->setLanguage($lang);

                    if ($settings->containsItems()) {
                        $itemData = json_decode($object->metaData['itemdata'], true);
                        $salesPriceService
                            ->setCurrency($itemData['currency'])
                            ->setShippingCountryId($itemData['countryId'])
                            ->setClassId($itemData['customerClassId']);
                    }

                    $templateContent = $this->twig->render(
                        $object->metaData['templatename'],
                        [
                            'options' => $settings->getData()
                        ]
                    );

                    $this->storageRepositoryContract->uploadObject(
                        'IO',
                        'tpl_' . $cacheObject->key,
                        $templateContent,
                        false,
                        $object->metaData
                    );

                    if (strlen((STRING)$templateContent) <= ContentCaching::MAX_BYTE_SIZE_FOR_FAST_CACHING) {
                        $smallContentCache          = pluginApp(SmallContentCache::class);
                        $smallContentCache->content = $templateContent;

                        $this->cachingRepository->put(substr('tpl_' . $cacheObject->key, 0, -5), $smallContentCache, 15);
                    } else {
                        $this->cachingRepository->forget(substr('tpl_' . $cacheObject->key, 0, -5));
                    }
                }
            } catch (\Exception $exc) {
                $this->getLogger('RebuildContentCache Job')->error($exc->getMessage());
                $this->storageRepositoryContract->deleteObject('IO', 'tpl_' . $cacheObject->key);
                $this->cachingRepository->forget(substr('tpl_' . $cacheObject->key, 0, -5));
            }
        }
    }
}