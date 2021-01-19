<?php

namespace IO\Helper;

/**
 * Interface for building context classes. These classes are used to pass data from PHP into TWIG.
 */
interface ContextInterface
{
    /**
     * Initialize the context and set the properties.
     * @param array Passthrough variables from the controller.
     */
    public function init($params);
}
