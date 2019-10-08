<?php

namespace IO\Helper;

use Plenty\Plugin\Application;
use Plenty\Plugin\Events\Dispatcher;

class EventDispatcher
{
    private const EVENT_PREFIX = 'IO.';
    private const INTERNAL_PREFIX = 'intl.';

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