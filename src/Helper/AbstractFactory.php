<?php //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Application;

class AbstractFactory
{
	/**
	 * @var Application
	 */
	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function make(string $className)
	{
		$instance = $this->app->make($className);
		if(!$instance instanceof $className)
		{
			throw new \Exception("Cannot create instance of class: " . $className);
		}
		return $instance;
	}
}
