<?php //strict

namespace IO\Services;

use Plenty\Plugin\Application;

/**
 * Service Class PropertyFileService
 *
 * This service class contains functions related to file properties.a
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class PropertyFileService
{
    /**
     * Get the URL where file properties are stored
     * @return string
     */
    public function getPropertyFileUrl(): string
    {
        /**
         * @var Application $application
         */
        $application = pluginApp(Application::class);

        return $application->getCdnDomain() . $application->getPlentyHash() . '/propertyItems/';
    }
}
