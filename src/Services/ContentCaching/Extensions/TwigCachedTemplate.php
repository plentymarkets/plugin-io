<?php

namespace IO\Services\ContentCaching\Extensions;

use IO\Services\ContentCaching\Services\ContentCaching;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Factories\TwigFactory;

/**
 * Created by ptopczewski, 14.06.17 09:01
 * Class TwigCachedTemplate
 * @package IO\Services\ContentCaching\Extensions
 */
class TwigCachedTemplate extends Twig_Extension
{
    /**
     * @var TwigFactory
     */
    private $twigFactory;
    /**
     * @var ContentCaching
     */
    private $contentCaching;

    /**
     * TwigCachedTemplate constructor.
     * @param TwigFactory $twigFactory
     * @param ContentCaching $contentCaching
     */
    public function __construct(TwigFactory $twigFactory, ContentCaching $contentCaching)
    {
        $this->twigFactory    = $twigFactory;
        $this->contentCaching = $contentCaching;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'IO_Extension_TwigCachedTemplate';
    }


    /**
     * Return a list of functions to add.
     *
     * @return array the list of functions to add.
     */
    public function getFunctions(): array
    {
        return [
            $this->twigFactory->createSimpleFunction(
                'includeCached',
                [$this, 'getCachedTemplate'],
                [
                    'is_safe' => ['html']
                ]
            )
        ];
    }

    /**
     * @param string $templateName
     * @return string
     */
    public function getCachedTemplate($templateName)
    {
        return $this->contentCaching->getContent($templateName);
    }

}