<?php //strict

namespace IO\Services;

use Plenty\Plugin\Application;

/**
 * Class PropertyFileService
 * @package IO\Services
 */
class PropertyFileService
{

    public function getPropertyFileUrl():string
    {
        /**
         * @var Application $application
         */
        $application = pluginApp(Application::class);

        return $application->getCdnDomain() . $application->getPlentyHash() . '/propertyItems/';
    }
}
