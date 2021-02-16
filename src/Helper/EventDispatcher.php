<?php

namespace IO\Helper;

use Plenty\Plugin\Application;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class EventDispatcher
 *
 * Helper class to fire events.
 *
 * @package IO\Helper
 */
class EventDispatcher
{
    /** @var string Event prefix for IO events */
    const EVENT_PREFIX = 'IO.';
    /** @var string Event prefix for internal events */
    const INTERNAL_PREFIX = 'intl.';

    /**
     * @param string $event Identifier of the event.
     * @param array $payload Additional data for the event.
     */
    public static function fire($event, $payload = [])
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = pluginApp(Dispatcher::class);

        /** @var Application $app */
        $app = pluginApp(Application::class);

        if ($app->isTemplateSafeMode()) {
            $event = self::INTERNAL_PREFIX . $event;
        }

        $dispatcher->fire(self::EVENT_PREFIX . $event, $payload);
    }
}
