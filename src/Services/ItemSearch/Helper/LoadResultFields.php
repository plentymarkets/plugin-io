<?php
namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Data\Contracts\Resources;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Created by ptopczewski, 09.01.17 11:07
 * Trait LoadResultFields
 * @package IO\Services\ItemSearch\Helper
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields
 */
trait LoadResultFields
{
	/**
	 * @param string $fullTemplateName
	 * @return array
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields::loadResultFields()
	 */
	private function loadResultFields($fullTemplateName)
	{
		/** @var Resources $resource */
		$resource = pluginApp(Resources::class);

		$resourcePath = explode('::', $fullTemplateName);
		$resourceName = $resourcePath[0] . '::views/' . str_replace('.', '/', $resourcePath[1]);
		if($resource->exists($resourceName . '.fields'))
		{
			return $resource->load($resourceName . '.fields')->getData();
		}

		/** @var LoggerFactory $loggerFactory */
		$loggerFactory = pluginApp(LoggerFactory::class);
		$loggerFactory
            ->getLogger("IO", __CLASS__)
            ->warning(
                "IO::Debug.LoadResultFields_notFound",
                [
                    "template"      => $fullTemplateName,
                    "resourceName"  => $resourceName . '.fields'
                ]
            );

		return [];
	}
}
