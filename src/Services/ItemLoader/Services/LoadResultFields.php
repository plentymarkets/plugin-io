<?php
namespace IO\Services\ItemLoader\Services;
use Plenty\Plugin\Data\Contracts\Resources;

/**
 * Created by ptopczewski, 09.01.17 11:07
 * Trait LoadResultFields
 * @package IO\Services\ItemLoader\Services
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
		if($resource->exists($resourceName ))
		{
			return $resource->load($resourceName)->getData();
		}
		return [];
	}
}