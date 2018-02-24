<?php
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Services\LoadResultFields;

/**
 * Created by ptopczewski, 09.01.17 09:44
 * Class ItemLoaderController
 * @package IO\Controllers
 *
 * @deprecated
 */
abstract class ItemLoaderController extends LayoutController
{
	use LoadResultFields;

	/**
	 * @param TemplateContainer $templateContainer
	 * @param null $customData
	 * @return TemplateContainer
	 */
	protected function prepareTemplateData(TemplateContainer $templateContainer, $customData = null):TemplateContainer
	{
		$resultFields = $this->loadResultFields($templateContainer->getTemplate());
		
		$loaderClassList = $customData['loadersList'];
		unset($customData['loadersList']);

		/** @var ItemLoaderService $itemLoader */
		$itemLoader = pluginApp(ItemLoaderService::class);
		$itemLoader
			->setLoaderClassList($loaderClassList)
			->setResultFields($resultFields)
			->setOptions($customData);
		
		$customData['itemLoader'] = $itemLoader;

		return parent::prepareTemplateData($templateContainer, $customData);
	}

	/**
	 * Emit an event to layout plugin to receive twig-template to use for current request.
	 * Add global template data to custom data from specific controller.
	 * Will pass request to the plentymarkets system if no template is provided by the layout plugin.
	 * @param string $templateEvent The event to emit to separate layout plugin
	 * @param array|string $loaderClassList
	 * @param array $templateData Additional template data from concrete controller
	 * @return string
	 */
	protected function renderItemTemplate(string $templateEvent, $loaderClassList, array $templateData = []):string
	{
		if(!is_array($loaderClassList))
		{
			$loaderClassList = [$loaderClassList];
		}

		$templateData['loadersList'] = $loaderClassList;

		return parent::renderTemplate($templateEvent, $templateData);
	}
}