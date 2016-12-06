<?php

namespace IO\Helper\Notifications;


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