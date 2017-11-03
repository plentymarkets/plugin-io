<?php
namespace IO\Services\ItemLoader\Services;

use IO\Services\ItemLoader\Contracts\FacetExtension;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class FacetExtensionContainer
 * @package IO\Services\ItemLoader\Services
 */
class FacetExtensionContainer
{
    /**
     * @var FacetExtension[]
     */
    private $facetExtensionsList = [];

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * FacetExtensionContainer constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return FacetExtension[]
     */
    public function getFacetExtensions()
    {
        if (empty($this->facetExtensionsList)) {
            $this->dispatcher->fire('IO.initFacetExtensions', [$this]);
        }

        return $this->facetExtensionsList;
    }

    /**
     * @param FacetExtension $facetExtension
     */
    public function addFacetExtension(FacetExtension $facetExtension)
    {
        $this->facetExtensionsList[] = $facetExtension;
    }
}