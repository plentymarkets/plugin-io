<?php
namespace IO\Services\ItemSearch\Helper;

use Plenty\Plugin\Data\Contracts\Resources;

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
		return [];
	}
}