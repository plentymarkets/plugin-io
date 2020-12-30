<?php //strict

namespace IO\Services;

use IO\Constants\LogLevel;
use IO\Middlewares\ClearNotifications;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;

/**
 * Service Class NotificationService
 *
 * This service class contains functions related to the notification functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class NotificationService
{
    /** @var SessionStorageRepositoryContract */
    private $sessionStorageRepository;

    /**
     * BasketService constructor.
     *
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     */
    public function __construct(SessionStorageRepositoryContract $sessionStorageRepository)
    {
        $this->sessionStorageRepository = $sessionStorageRepository;
    }

    /**
     * Get a list of all notifications stored in the session
     * @param bool $clear Optional: If true, notifications are cleared from the session afterwards (Default: true)
     * @return array
     */
    public function getNotifications($clear = true): array
    {
        $notifications = json_decode(
            $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS),
            true
        );

        if ($notifications == null || !is_array($notifications) || !count($notifications)) {
            $notifications = [
                "error" => null,
                "warn" => null,
                "info" => null,
                "success" => null,
                "log" => null
            ];
        }

        if ($clear) {
            ClearNotifications::$CLEAR_NOTIFICATIONS = true;
        }

        return $notifications;
    }

    /**
     * Add a notification to the sessions notification list
     * @param string $message The notifications message
     * @param string $type The type of notification, see /IO/Constants/LogLevel
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    private function addNotification(string $message, string $type, int $code = 0, array $placeholder = null)
    {
        $notifications = $this->getNotifications(false);
        if (!array_key_exists($type, $notifications)) {
            $type = LogLevel::ERROR;
        }

        $notification = [
            'message' => $message,
            'code' => $code,
            'stackTrace' => [],
            'placeholder' => $placeholder
        ];
        $lastNotification = $notifications[$type];

        if (!is_null($lastNotification)) {
            $notification['stackTrace'] = $lastNotification['stackTrace'];
            $lastNotification['stackTrace'] = [];
            array_push($notification['stackTrace'], $lastNotification);
        }

        $notifications[$type] = $notification;

        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::NOTIFICATIONS,
            json_encode($notifications)
        );
    }

    /**
     * Shorthand for addNotification() with LogLevel::LOG
     * @param string $message The notifications message
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function log(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::LOG, $code, $placeholder);
    }

    /**
     * Shorthand for addNotification() with LogLevel::INFO
     * @param string $message The notifications message
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function info(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::INFO, $code, $placeholder);
    }

    /**
     * Shorthand for addNotification() with LogLevel::WARN
     * @param string $message The notifications message
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function warn(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::WARN, $code, $placeholder);
    }

    /**
     * Shorthand for addNotification() with LogLevel::ERROR
     * @param string $message The notifications message
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function error(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::ERROR, $code, $placeholder);
    }

    /**
     * Shorthand for addNotification() with LogLevel::SUCCESS
     * @param string $message The notifications message
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function success(string $message, $code = 0, array $placeholder = null)
    {
        $this->addNotification($message, LogLevel::SUCCESS, $code, $placeholder);
    }

    /**
     * Shorthand for addNotification() with empty message and variable type
     * @param string $type The type of notification
     * @param int $code Optional: Message code (Default: 0)
     * @param array|null $placeholder Optional: A placeholder
     */
    public function addNotificationCode($type, int $code = 0, array $placeholder = null)
    {
        $this->addNotification("", $type, $code, $placeholder);
    }

    /**
     * Check if the session currently has any notifications
     * @return bool
     */
    public function hasNotifications()
    {
        $notifications = json_decode(
            $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS),
            true
        );
        return is_array($notifications) && count($notifications) > 0;
    }

    /**
     * Clear existing notifications
     */
    public function clearNotifications()
    {
        $this->sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS, json_encode([]));
    }
}
