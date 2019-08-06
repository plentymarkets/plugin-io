<?php
namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Data\Contracts\Resources;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Created by ptopczewski, 09.01.17 11:07
 * Trait LoadResultFields
 * @package IO\Services\ItemSearch\Helper
 */
trait LoadResultFields
{
	/**
	 * @param string $fullTemplateName
	 * @return array
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

        pluginApp(LoggerFactory::class)
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