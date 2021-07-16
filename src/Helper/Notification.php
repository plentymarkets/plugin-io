<?php

namespace IO\Helper\Notifications;

/**
 * Class Notification
 *
 * Helper class for notifications.
 *
 * @package IO\Helper\Notifications
 * @deprecated since 5.0.0, will be removed in 6.0.0.
 * @see \IO\Services\NotificationService
 */
class Notification
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    /**
     * Notification constructor.
     * @param string $message
     * @param string $type
     */
    public function __construct(string $message, string $type)
    {
        $this->message = $message;
        $this->type = $type;
    }
}
