<?php //strict

namespace IO\Services;

use IO\Constants\SessionStorageKeys;
use IO\Constants\LogLevel;

/**
 * Class BasketService
 * @package IO\Services
 */
class NotificationService
{
    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

    /**
     * BasketService constructor.
     * @param \IO\Services\SessionStorageService $sessionStorageService
     */
    public function __construct(SessionStorageService $sessionStorageService)
    {
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * @param bool $clear
     * @return array
     */
    public function getNotifications($clear = true):array
    {
        $notifications = json_decode($this->sessionStorageService->getSessionValue(SessionStorageKeys::NOTIFICATIONS));

        if ($notifications == null || !is_array($notifications))
        {
            $notifications = array();
        }

        if ($clear)
        {
            $this->sessionStorageService->setSessionValue(SessionStorageKeys::NOTIFICATIONS, json_encode(array()));
        }

        return $notifications;
    }

    /**
     * @param string $message
     * @param string $type
     */
    private function addNotification(string $message, string $type)
    {
        $notifications = $this->getNotifications(false);

        array_push($notifications, array(
            'message' => $message,
            'type' => $type
        ));

        $this->sessionStorageService->setSessionValue(SessionStorageKeys::NOTIFICATIONS, json_encode($notifications));
    }

    /**
     * @param string $message
     */
    public function log(string $message)
    {
        $this->addNotification($message, LogLevel::LOG);
    }

    /**
     * @param string $message
     */
    public function info(string $message)
    {
        $this->addNotification($message, LogLevel::INFO);
    }

    /**
     * @param string $message
     */
    public function warn(string $message)
    {
        $this->addNotification($message, LogLevel::WARN);
    }

    /**
     * @param string $message
     */
    public function error(string $message)
    {
        $this->addNotification($message, LogLevel::ERROR);
    }

    /**
     * @param string $message
     */
    public function success(string $message)
    {
        $this->addNotification($message, LogLevel::SUCCESS);
    }
}
