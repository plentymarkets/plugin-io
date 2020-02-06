<?php
namespace IO\Services\ItemSearch\Helper;

use IO\Services\ItemSearch\Contracts\FacetExtension;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class FacetExtensionContainer
 * @package IO\Services\ItemSearch\Helper
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\FacetExtensionContainer
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\FacetExtensionContainer::getFacetExtensions()
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\FacetExtensionContainer::addFacetExtension()
     */
    public function addFacetExtension(FacetExtension $facetExtension)
    {
        $this->facetExtensionsList[] = $facetExtension;
    }
}
