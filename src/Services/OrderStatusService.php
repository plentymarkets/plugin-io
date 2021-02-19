<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;
use Plenty\Modules\Order\Status\Models\OrderStatus;
use Plenty\Modules\Order\StatusHistory\Contracts\StatusHistoryRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class OrderStatusService
 *
 * This service class contains functions related to the order status.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class OrderStatusService
{
    use Loggable;

    /** @var AuthHelper */
    private $authHelper;

    /** @var OrderStatusRepositoryContract */
    private $orderStatusRepo;

    /** @var StatusHistoryRepositoryContract */
    private $statusHistoryRepo;

    /**
     * OrderStatusService constructor.
     * @param AuthHelper $authHelper
     * @param OrderStatusRepositoryContract $orderStatusRepo
     * @param StatusHistoryRepositoryContract $statusHistoryRepo
     */
    public function __construct(
        AuthHelper $authHelper,
        OrderStatusRepositoryContract $orderStatusRepo,
        StatusHistoryRepositoryContract $statusHistoryRepo
    )
    {
        $this->authHelper = $authHelper;
        $this->orderStatusRepo = $orderStatusRepo;
        $this->statusHistoryRepo = $statusHistoryRepo;
    }

    /**
     * Get the status of a specific order
     * @param int $orderId The id of the order to find order status for
     * @param float $orderStatusId The id of the order status to find
     * @return string
     * @throws \Throwable
     */
    public function getOrderStatus($orderId, $orderStatusId)
    {
        $lang = Utils::getLang();
        $orderStatusRepo = $this->orderStatusRepo;
        $statusHistoryRepo = $this->statusHistoryRepo;
        $logger = $this->getLogger(__CLASS__)->addReference('orderId', $orderId);

        $orderStatus = $this->authHelper->processUnguarded(function () use (
            $orderId,
            $orderStatusId,
            $lang,
            $orderStatusRepo,
            $statusHistoryRepo,
            $logger
        ) {
            $statusHistory = $statusHistoryRepo->getStatusHistoryByOrderId($orderId);
            $orderStatus = $orderStatusRepo->get($orderStatusId);
            if (!is_null($orderStatus) && $orderStatus->isFrontendVisible) {
                return str_replace('[' . $orderStatus->statusId . ']', '', $orderStatus->names->get($lang));
            } elseif (!is_null($orderStatus)) {
                $statusHistory = $statusHistoryRepo->getStatusHistoryByOrderId($orderId);
                if (count($statusHistory)) {
                    $statusHistoryNew = [];
                    foreach ($statusHistory as $entryKey => $entry) {
                        // status with 0 means the creation of an order is not completed, caused by an error
                        if ($entry->statusId > 0) {
                            try {
                                $statusHistoryNew[] = $orderStatusRepo->get($entry->statusId);
                            } catch (\Exception $e) {
                                $logger->debug("IO::Debug.OrderStatusService_getOrderStatus", [
                                    'code' => $e->getCode(),
                                    'message' => $e->getMessage(),
                                    'entryKey' => $entryKey
                                ]);

                            }
                        }
                    }

                    if (count($statusHistoryNew)) {
                        for ($i = count($statusHistoryNew) - 1; $i >= 0; $i--) {
                            $statusEntry = $statusHistoryNew[$i];
                            if ($statusEntry instanceof OrderStatus && $statusEntry->statusId < $orderStatusId && $statusEntry->isFrontendVisible) {
                                return str_replace('[' . $statusEntry->statusId . ']', '',
                                    $statusEntry->names->get($lang));
                            }
                        }
                    }
                }
            }

            return '';
        });

        return $orderStatus;
    }
}
