<?php

namespace IO\Helper;

/**
 * Interface ContextInterface
 *
 * The interface for context classes.
 * Context classes contain data used for rendering twig templates.
 *
 * @package IO\Helper
 */
interface ContextInterface
{
    /**
     * Init function for context classes.
     * @param array $params Raw data for the context
     * @return mixed
     */
    public function init($params);
}
