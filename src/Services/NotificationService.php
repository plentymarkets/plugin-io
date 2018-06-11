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
        $notifications = json_decode($this->sessionStorageService->getSessionValue(SessionStorageKeys::NOTIFICATIONS), true);

        if ($notifications == null || !is_array($notifications) || !count($notifications) )
        {
            $notifications = [
                "error"     => null,
                "warn"      => null,
                "info"      => null,
                "success"   => null,
                "log"       => null
            ];
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
     * @param int $code
     */
    private function addNotification(string $message, string $type, int $code = 0)
    {
        $notifications = $this->getNotifications(false);
        if ( !array_key_exists($type, $notifications) )
        {
            $type = LogLevel::ERROR;
        }

        $notification = [
            'message'       => $message,
            'code'          => $code,
            'stackTrace'    => []
        ];
        $lastNotification = $notifications[$type];

        if ( !is_null($lastNotification) )
        {
            $notification['stackTrace'] = $lastNotification['stackTrace'];
            $lastNotification['stackTrace'] = [];
            array_push( $notification['stackTrace'], $lastNotification );
        }

        $notifications[$type] = $notification;

        $this->sessionStorageService->setSessionValue(SessionStorageKeys::NOTIFICATIONS, json_encode($notifications));
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function log(string $message, $code = 0)
    {
        $this->addNotification($message, LogLevel::LOG, $code);
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function info(string $message, $code = 0)
    {
        $this->addNotification($message, LogLevel::INFO, $code);
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function warn(string $message, $code = 0)
    {
        $this->addNotification($message, LogLevel::WARN, $code);
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function error(string $message, $code = 0)
    {
        $this->addNotification($message, LogLevel::ERROR, $code);
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function success(string $message, $code = 0)
    {
        $this->addNotification($message, LogLevel::SUCCESS, $code);
    }

    /**
     * @param $type
     * @param int $code
     */
    public function addNotificationCode($type, int $code = 0)
    {
        $this->addNotification("", $type, $code);
    }
}
