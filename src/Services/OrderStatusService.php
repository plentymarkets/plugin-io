<?php

namespace IO\Services;

use Illuminate\Database\Eloquent\Collection;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Status\Contracts\OrderStatusRepositoryContract;
use Plenty\Modules\Order\Status\Models\OrderStatus;
use Plenty\Modules\Order\StatusHistory\Contracts\StatusHistoryRepositoryContract;

/**
 * Class OrderStatusService
 * @package IO\Services
 */
class OrderStatusService
{
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
    public function __construct(AuthHelper $authHelper, OrderStatusRepositoryContract $orderStatusRepo, StatusHistoryRepositoryContract $statusHistoryRepo)
    {
        $this->authHelper = $authHelper;
        $this->orderStatusRepo = $orderStatusRepo;
        $this->statusHistoryRepo = $statusHistoryRepo;
    }
    
    /**
     * @param int $orderId
     * @param float $orderStatusId
     * @return mixed
     */
    public function getOrderStatus($orderId, $orderStatusId)
    {
        $lang = pluginApp(SessionStorageService::class)->getLang();
        
        $orderStatusRepo = $this->orderStatusRepo;
        $statusHistoryRepo = $this->statusHistoryRepo;
        
        $orderStatus = $this->authHelper->processUnguarded( function() use ($orderId, $orderStatusId, $lang, $orderStatusRepo, $statusHistoryRepo)
        {
            $orderStatus = $orderStatusRepo->get($orderStatusId);
            if ( !is_null($orderStatus) && $orderStatus->isFrontendVisible )
            {
                return str_replace('['.$orderStatus->statusId.']', '', $orderStatus->names->get($lang));
            }
            elseif( !is_null($orderStatus) )
            {
                $statusHistory = $statusHistoryRepo->getStatusHistoryByOrderId($orderId);
                if(count($statusHistory))
                {
                    $statusHistoryNew = array_map(function($entry) use ($orderStatusRepo) {
                        return $orderStatusRepo->get($entry['statusId']);
                    }, $statusHistory->toArray());
                    
                    if(count($statusHistoryNew))
                    {
                        for($i = count($statusHistoryNew)-1; $i >= 0; $i--)
                        {
                            $statusEntry = $statusHistoryNew[$i];
                            if($statusEntry instanceof OrderStatus && $statusEntry->statusId < $orderStatusId && $statusEntry->isFrontendVisible)
                            {
                                return str_replace('['.$statusEntry->statusId.']', '', $statusEntry->names->get($lang));
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