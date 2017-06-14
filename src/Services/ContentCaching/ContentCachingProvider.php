<?php

namespace IO\Services\ContentCaching;

use IO\Services\ContentCaching\Extensions\TwigCachedTemplate;
use IO\Services\ContentCaching\Services\Container;
use IO\Services\ContentCaching\Services\ContentCaching;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

/**
 * Created by ptopczewski, 14.06.17 10:59
 * Class ContentCachingProvider
 * @package IO\Services\ContentCaching
 */
class ContentCachingProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->getApplication()->singleton(Container::class);
        $this->getApplication()->singleton(ContentCaching::class);
    }

    /**
     * @param Twig $twig
     */
    public function boot(Twig $twig)
    {
        $twig->addExtension(TwigCachedTemplate::class);
    }
}