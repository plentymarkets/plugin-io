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
     * @param array|null $placeholder
     */
    private function addNotification(string $message, string $type, int $code = 0, array $placeholder = null)
    {
        $notifications = $this->getNotifications(false);
        if ( !array_key_exists($type, $notifications) )
        {
            $type = LogLevel::ERROR;
        }

        $notification = [
            'message'       => $message,
            'code'          => $code,
            'stackTrace'    => [],
            'placeholder'          => $placeholder
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
     * @param array $placeholder
     */
    public function log(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::LOG, $code, $placeholder);
    }

    /**
     * @param string $message
     * @param int $code
     * @param array|null $placeholder
     */
    public function info(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::INFO, $code, $placeholder);
    }

    /**
     * @param string $message
     * @param int $code
     * @param array|null $placeholder
     */
    public function warn(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::WARN, $code, $placeholder);
    }

    /**
     * @param string $message
     * @param int $code
     * @param array|null $placeholder
     */
    public function error(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::ERROR, $code, $placeholder);
    }

    /**
     * @param string $message
     * @param int $code
     * @param array|null $placeholder
     */
    public function success(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::SUCCESS, $code, $placeholder);
    }

    /**
     * @param $type
     * @param int $code
     * @param array|null $placeholder
     */
    public function addNotificationCode($type, int $code = 0, array $placeholder = null)
    {
        $this->addNotification("", $type, $code, $placeholder);
    }
}
